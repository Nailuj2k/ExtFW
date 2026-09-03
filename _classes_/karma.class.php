<?php

/*

Karma Points: Each user now has a user_score column in the users table, initialized to 0.

Earning or lose Karma
Users earn 10 points for each post they create. (pages,news, blog entries, etc.)
Users earn 5 points for each comment they make.
Users earn/lose points from post ratings (rating - 2): 5★ +3, 4★ +2, 3★ +1, 2★ 0, 1★ -1, 0★ -2.
Users earn 1 point for each upvote their comments receive and lose 1 point for each downvote.
Extra rewards/pens: profile complete, streaks, primer aporte por categoría, solución aceptada, reporte válido; penalizaciones por reporte falso, flood, autovoto detectado.
Protecciones: ganchos para tope diario de gasto/pérdida y recuperación mínima (requiere persistencia de métricas diarias).

Spending Karma
Users can spend points to rating posts and vote comments. This functionality can be implemented in the application logic by checking the user's available points before allowing a vote.
The rating system has been updated to allow ranging from 0 to 5, with corresponding point costs.

Rating Value and Cost
Rating 5: Costs 5 points
Rating 4: Costs 4 points
Rating 3: Costs 3 points
Rating 2: Costs 2 points
Rating 1: Costs 1 point
Rating 0: Costs 2 points

El sistema deduce el número apropiado de puntos de karma de la puntuación del votante y ajusta el karma del propietario de la publicación en función del valor del voto. Esto asegura que los usuarios deben administrar sus puntos de karma sabiamente al votar.

*/


class Karma extends DbConnection {

    // if not enabled ,no points are given or taken and all functions return true/0 as appropriate
    // and user can vote and rating withot spending points
    public static $enabled = true;

    public const KARMA_DAILY_TABLE    = 'POST_KARMA_DAILY';

    // Earning
    public const POST_CREATED    = 10; // Points for publishing a post
    public const COMMENT_POSTED  = 5;  // Points for posting a comment
    public const POST_UPVOTED    = 1;  // Points granted to the owner when their post is upvoted
    public const POST_DOWNVOTED  = -1; // Points removed from the owner when their post is downvoted
    public const PROFILE_COMPLETED = 3; // Small boost for completing profile
    public const ACCEPTED_SOLUTION = 8; // When user marks/gets an accepted solution
    public const VALID_REPORT      = 4; // Reward for a validated report
    public const FIRST_CATEGORY_POST = 5; // First contribution in a category
    public const STREAK_BASE       = 2; // Base for streak bonus.     
    public const STREAK_PER_DAY    = 1; // Increment per day in streak (cap logic left to caller)
    // Nuevos eventos de ganancia
    public const EMAIL_VERIFIED      = 5;
    public const AVATAR_UPLOADED     = 2;
    public const BIO_COMPLETED       = 2;
    public const OAUTH_CONNECTED     = 3;
    public const DAILY_LOGIN         = 1;
    public const ANNIVERSARY_BONUS   = 20;  // Por año
    public const CONTENT_SHARED      = 2;
    public const CONTENT_BOOKMARKED  = 1;   // Cuando alguien guarda tu contenido
    public const REPLY_TO_COMMENT    = 1;   // Responder comentario de otro
    public const PURCHASE_MADE       = 3;   // Compra en tienda

    public const TIP_RECEIVED    = 10;   // Por cada 1000 sats recibidos
    public const TIP_GIVEN       = 8;   // Por cada 1000 sats dados (fomenta generosidad)
    public const MIN_TIP_RECEIVED    = 1;   // Mín al recibir propina
    public const MIN_TIP_GIVEN       = 1;   // Mín al dar propina
    public const MAX_TIP_RECEIVED    = 25;   // Máx al recibir propina
    public const MAX_TIP_GIVEN       = 35;   // Máx al dar propina
    
    public const PENALTY_FALSE_REPORT = -4;
    public const PENALTY_FLOOD        = -2;
    public const PENALTY_SELF_VOTE    = -5;
    // Nuevas penalizaciones
    public const PENALTY_COMMENT_REMOVED = -5;
    public const PENALTY_SPAM_CONTENT    = -10;
    public const PENALTY_ACCOUNT_WARNED  = -15;

    // Spending
    public const COMMENT_VOTE_COST = 1; // Cost to vote a comment (up/down/meh)

    // Rating configuration
    public static $maxStars = 5;  // Cambiar a 10 para usar 10 estrellas (ahora es variable, no constante)
    public const RATING_ZERO_COST = 4;
    public const RATING_ZERO_IMPACT = -2;

    // Optional limits (0 means disabled; requires implementing persistence in getDailySpent/logDailySpend)
    public const DAILY_SPEND_LIMIT = 0; // Max points a user can spend per day
    public const DAILY_LOSS_LIMIT  = 0; // Max points a user can lose per day (e.g., downvotes)
    public const MINIMUM_DAILY_RECOVERY = 10; // Daily top-up to avoid user starvation (0 = off)

    

    protected static $dailyTableReady = false;

    /**
     * Add points to a user's karma score. $points can be positive or negative.
     */
    public static function addPoints($user_id, $points) {
        if (!self::$enabled) {
            return;
        }
        if ($user_id > 0 && $points != 0) {
            self::sqlQueryPrepared("UPDATE ".TB_USER." SET user_score = COALESCE(user_score, 0) + ? WHERE user_id = ?", [intval($points), intval($user_id)]);
        }
    }

    /**
     * Fetch current karma for a user.
     */
    public static function getUserScore($user_id) {
        if (!self::$enabled) {
            return 0;
        }
        if ($user_id <= 0) {
            return null;
        }
        $row = self::sqlQueryPrepared("SELECT user_score FROM ".TB_USER." WHERE user_id = ?", [intval($user_id)]);
        return $row[0]['user_score'] ?? null;
    }

    /**
     * Check if the user can spend the given amount of points.
     */
    public static function canSpend($user_id, $points) {
        if (!self::$enabled) {
            return true;
        }
        $points = abs(intval($points));
        $score = self::getUserScore($user_id);
        return $score !== null && $score >= $points;
    }

    /**
     * Hooks for daily spend tracking. Override to use real persistence.
     */
    protected static function getDailySpent($user_id) {
        if (!self::ensureDailyTable()) {
            return 0;
        }
        $row = self::sqlQueryPrepared(
            "SELECT spent FROM ".self::KARMA_DAILY_TABLE." WHERE user_id = ? AND date_key = ? LIMIT 1",
            [intval($user_id), self::todayKey()]
        );
        return ($row && isset($row[0]['spent'])) ? intval($row[0]['spent']) : 0;
    }

    protected static function logDailySpend($user_id, $points) {
        if (!self::ensureDailyTable()) {
            return;
        }
        self::upsertDailyRow($user_id, self::todayKey(), intval($points), 0);
    }

    protected static function wouldExceedDailySpend($user_id, $points) {
        if ($user_id <= 0) {
            return false;
        }
        $todaySpent = self::getDailySpent($user_id);
        return ($todaySpent + $points) > self::DAILY_SPEND_LIMIT;
    }

    protected static function dailyTableExists() {
        $table = self::KARMA_DAILY_TABLE;
        $dbType = self::dbType();
        if ($dbType === 'sqlite') {
            $row = self::sqlQueryPrepared(
                "SELECT name FROM sqlite_master WHERE type='table' AND name = ? LIMIT 1",
                [$table],
                false
            );
            return !empty($row);
        }

        // MySQL/MariaDB default
        $row = self::sqlQueryPrepared(
            "SHOW TABLES LIKE ?",
            [$table],
            false
        );
        return !empty($row);
    }

    protected static function ensureDailyTable() {
        if (self::$dailyTableReady) {
            return true;
        }

        // Normal case: table created during installation.
        // Avoid running DDL on every request by checking existence first.
        if (self::dailyTableExists()) {
            self::$dailyTableReady = true;
            return true;
        }

        // Fallback for older installs: attempt to create once.
        $sql = "CREATE TABLE IF NOT EXISTS ".self::KARMA_DAILY_TABLE." (
            user_id INTEGER NOT NULL,
            date_key CHAR(10) NOT NULL,
            spent INTEGER NOT NULL DEFAULT 0,
            loss INTEGER NOT NULL DEFAULT 0,
            PRIMARY KEY (user_id, date_key)
        )";
        $ok = self::sqlQueryPrepared($sql, [], false);
        self::$dailyTableReady = ($ok !== false);
        return self::$dailyTableReady;
    }

    protected static function todayKey() {
        return date('Y-m-d');
    }

    protected static function dbType() {
        return strtolower(CFG::$vars['db']['type'] ?? 'mysql');
    }

    protected static function upsertDailyRow($user_id, $dateKey, $spentDelta, $lossDelta) {
        $user_id = intval($user_id);
        if ($user_id <= 0) {
            return;
        }
        $spentDelta = intval($spentDelta);
        $lossDelta  = intval($lossDelta);
        $dbType = self::dbType();
        if ($dbType === 'sqlite') {
            $sql = "INSERT INTO ".self::KARMA_DAILY_TABLE." (user_id, date_key, spent, loss)
                    VALUES (?, ?, ?, ?)
                    ON CONFLICT(user_id, date_key) DO UPDATE SET
                        spent = spent + excluded.spent,
                        loss  = loss + excluded.loss";
            self::sqlQueryPrepared($sql, [$user_id, $dateKey, $spentDelta, $lossDelta], false);
        } else { // default MySQL/MariaDB
            $sql = "INSERT INTO ".self::KARMA_DAILY_TABLE." (user_id, date_key, spent, loss)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        spent = spent + VALUES(spent),
                        loss  = loss + VALUES(loss)";
            self::sqlQueryPrepared($sql, [$user_id, $dateKey, $spentDelta, $lossDelta], false);
        }
    }

    /**
     * Deduct points from the user if possible.
     */
    public static function spendPoints($user_id, $points) {
        if (!self::$enabled) {
            return true;
        }
        $points = abs(intval($points));
        if ($points === 0 || $user_id <= 0) {
            return false;
        }

        if (self::DAILY_SPEND_LIMIT > 0 && self::wouldExceedDailySpend($user_id, $points)) {
            return false;
        }

        if (!self::canSpend($user_id, $points)) {
            return false;
        }

        // Intentar deducir; si quedara negativo, revertir
        self::addPoints($user_id, -$points);
        $newScore = self::getUserScore($user_id);
        if ($newScore === null || $newScore < 0) {
            self::addPoints($user_id, $points); // revert
            return false;
        }

        self::logDailySpend($user_id, $points);
        return true;
    }

    protected static function applyLossWithLimit($user_id, $lossPoints) {
        if (!self::$enabled) {
            return;
        }
        $lossPoints = abs(intval($lossPoints));
        if ($lossPoints === 0 || $user_id <= 0) {
            return;
        }
        if (self::DAILY_LOSS_LIMIT > 0 && self::wouldExceedDailyLoss($user_id, $lossPoints)) {
            return;
        }
        self::addPoints($user_id, -$lossPoints);
        self::logDailyLoss($user_id, $lossPoints);
    }

    protected static function getDailyLoss($user_id) {
        if (!self::ensureDailyTable()) {
            return 0;
        }
        $row = self::sqlQueryPrepared(
            "SELECT loss FROM ".self::KARMA_DAILY_TABLE." WHERE user_id = ? AND date_key = ? LIMIT 1",
            [intval($user_id), self::todayKey()]
        );
        return ($row && isset($row[0]['loss'])) ? intval($row[0]['loss']) : 0;
    }

    protected static function logDailyLoss($user_id, $lossPoints) {
        if (!self::ensureDailyTable()) {
            return;
        }
        self::upsertDailyRow($user_id, self::todayKey(), 0, intval($lossPoints));
    }

    protected static function wouldExceedDailyLoss($user_id, $lossPoints) {
        if ($user_id <= 0) {
            return false;
        }
        $todayLoss = self::getDailyLoss($user_id);
        return ($todayLoss + $lossPoints) > self::DAILY_LOSS_LIMIT;
    }

    /**
     * Reward a user for creating a post.
     */
    public static function rewardPostCreated($user_id) {
        if (!self::$enabled) {
            return;
        }
        self::addPoints($user_id, self::POST_CREATED);
    }

    /**
     * Reward a user for creating a comment.
     */
    public static function rewardCommentPosted($user_id) {
        if (!self::$enabled) {
            return;
        }
        self::addPoints($user_id, self::COMMENT_POSTED);
    }

    /**
     * Reward user for completing profile.
     * Example hook: after user saves all required profile fields → rewardProfileCompleted($userId);
     */
    public static function rewardProfileCompleted($user_id) {
        if (!self::$enabled) {
            return;
        }
        self::addPoints($user_id, self::PROFILE_COMPLETED);
    }

    /**
     * Reward first contribution in a category.
     * Example hook: upon first post in category X → rewardFirstCategoryPost($userId);
     */
    public static function rewardFirstCategoryPost($user_id) {
        if (!self::$enabled) {
            return;
        }
        self::addPoints($user_id, self::FIRST_CATEGORY_POST);
    }

    /**
     * Reward accepted solution (solver or marker).
     * Example hook: when a post/comment is marked as solution → rewardAcceptedSolution($solverId);
     */
    public static function rewardAcceptedSolution($user_id) {
        if (!self::$enabled) {
            return;
        }
        self::addPoints($user_id, self::ACCEPTED_SOLUTION);
    }

    /**
     * Reward a validated report.
     * Example hook: when a moderator marks a report as valid → rewardValidReport($reporterId);
     */
    public static function rewardValidReport($user_id) {
        if (!self::$enabled) {
            return;
        }
        self::addPoints($user_id, self::VALID_REPORT);
    }

    /**
     * Reward a tip received.   
     * Example hook: when user receives a tip → rewardTipReceived($userId, $amount);
     */
    public static function rewardTipReceived($user_id, $amount) {
        if (!self::$enabled) {
            return;
        }
        // Tips are in satoshis; award 1 point per 10 sats, capped by TIP_RECEIVED.
        $amount = max(0, intval($amount));
        if ($amount < 10) {
            return; // below minimum tip granularity
        }
        $bonus = min(intdiv($amount, 10), self::TIP_RECEIVED);
        if ($bonus > 0) {
            self::addPoints($user_id, $bonus);
        }
    }

    /**
     * Reward a tip given.
     * Example hook: when user gives a tip → rewardTipGiven($userId, $amount);
     */
    public static function rewardTipGiven($user_id, $amount) {
        if (!self::$enabled) {
            return;
        }
        // Tips are in satoshis; award 1 point per 20 sats, capped by TIP_GIVEN.
        $amount = max(0, intval($amount));
        if ($amount < 20) {
            return; // below minimum tip granularity
        }
        $bonus = min(intdiv($amount, 20), self::TIP_GIVEN);
        if ($bonus > 0) {
            self::addPoints($user_id, $bonus);
        }
    }

    /**
     * Reward streaks of contributions. Caller provides streak length (days/posts).
     * Example hook: after computing streak length for user → rewardPostStreak($userId, $streakDays);
     */
    public static function rewardPostStreak($user_id, $streakLength) {
        if (!self::$enabled) {
            return;
        }
        $streakLength = max(1, intval($streakLength));
        $bonus = self::STREAK_BASE + (($streakLength - 1) * self::STREAK_PER_DAY);
        self::addPoints($user_id, $bonus);
    }

    /**
     * Adjust the owner's karma when their post receives an up/down vote.
     */
    public static function applyPostVoteImpact($post_owner_id, $vote) {
        if (!self::$enabled) {
            return;
        }
        $delta = 0;
        if ($vote === 'up') {
            $delta = self::POST_UPVOTED;
        } elseif ($vote === 'down') {
            $delta = self::POST_DOWNVOTED;
        }

        if ($delta !== 0 && $post_owner_id > 0) {
            if ($delta < 0) {
                self::applyLossWithLimit($post_owner_id, abs($delta));
            } else {
                self::addPoints($post_owner_id, $delta);
            }
        }
    }

    /**
     * Cost of rating a post for a given value (0..MAX_STARS).
     * Rating 0 (prohibido) tiene coste especial definido en RATING_ZERO_COST.
     * El resto: coste = valor del rating.
     */
    public static function ratingCost($rating) {
        $rating = intval($rating);
        
        if ($rating < 0 || $rating > self::$maxStars) {
            return null;
        }
        
        // El prohibido (0) tiene coste especial
        if ($rating === 0) {
            return self::RATING_ZERO_COST;
        }
        
        // El resto: coste = valor del rating
        return $rating;
    }

    /**
     * Impact on the owner's karma for a given rating value.
     * Rating 0 (prohibido) = penalización especial definida en RATING_ZERO_IMPACT.
     * El resto: impacto = valor del rating (1 a MAX_STARS).
     */
    public static function ratingImpactOnOwner($rating) {
        $rating = intval($rating);
        
        // El prohibido (0) tiene un impacto negativo especial
        if ($rating === 0) {
            return self::RATING_ZERO_IMPACT;
        }
        
        // El resto: impacto = valor del rating
        return $rating;
    }

    /**
     * Impact on comment owner for votes (up/down/meh).
     */
    public static function commentVoteImpact($vote) {
        $map = ['up' => 1, 'down' => -1, 'meh' => 0];
        return $map[strtolower($vote)] ?? 0;
    }

    /**
     * Aplica un rating: descuenta al votante y ajusta karma del autor.
     * Si $previousRating se aporta y es igual al nuevo, no hace nada.
     */
    public static function applyRating($voter_id, $post_owner_id, $rating, $previousRating = null) {
        if (!self::$enabled) {
            return ['error' => 0, 'msg' => 'Karma disabled. No cost applied.', 'cost' => 0, 'owner_delta' => 0];
        }
        $result = ['error' => 0, 'msg' => '', 'cost' => 0, 'owner_delta' => 0, 'refund' => 0];
        $rating = intval($rating);

        // Validar rango usando $maxStars (variable estática)
        if ($rating < 0 || $rating > self::$maxStars) {
            return ['error' => 1, 'msg' => 'Invalid rating value.'];
        }

        if ($post_owner_id > 0 && $post_owner_id === $voter_id) {
            return ['error' => 1, 'msg' => 'You cannot rate your own post.'];
        }

        // Normalizar previo - convertir a int si no es null
        if ($previousRating !== null) {
            $previousRating = intval($previousRating);
            if ($previousRating < 0 || $previousRating > self::$maxStars) {
                $previousRating = null;
            }
        }

        // Comparar después de normalizar ambos a int
        if ($previousRating !== null && $previousRating === $rating) {
            return ['error' => 0, 'msg' => 'Rating unchanged.', 'cost' => 0, 'owner_delta' => 0, 'refund' => 0];
        }

        // Calcular coste/devolución
        $newCost = self::ratingCost($rating);
        if ($newCost === null) {
            return ['error' => 1, 'msg' => 'Rating cost not defined.'];
        }

        $cost = 0;
        $refund = 0;

        if ($previousRating !== null) {
            $prevCost = self::ratingCost($previousRating);
            $costDelta = $newCost - $prevCost;
            
            if ($costDelta > 0) {
                // Subir rating: cobrar diferencia
                $cost = $costDelta;
            } else if ($costDelta < 0) {
                // Bajar rating: devolver diferencia
                $refund = abs($costDelta);
            }
            // Si costDelta == 0, no hay coste ni devolución
        } else {
            // Primer rating: cobrar coste completo
            $cost = $newCost;
        }

        // Cobrar si hay coste > 0
        if ($cost > 0) {
            if (!self::canSpend($voter_id, $cost)) {
                return ['error' => 1, 'msg' => 'Insufficient karma points to rate.'];
            }

            if (!self::spendPoints($voter_id, $cost)) {
                return ['error' => 1, 'msg' => 'Insufficient karma points to rate.'];
            }
        }

        // Devolver si hay refund > 0
        if ($refund > 0) {
            self::addPoints($voter_id, $refund);
        }

        $impactNew  = self::ratingImpactOnOwner($rating);
        $impactPrev = ($previousRating !== null) ? self::ratingImpactOnOwner($previousRating) : 0;
        $ownerDelta = $impactNew - $impactPrev;

        if ($post_owner_id > 0 && $post_owner_id !== $voter_id && $ownerDelta !== 0) {
            if ($ownerDelta < 0) {
                self::applyLossWithLimit($post_owner_id, abs($ownerDelta));
            } else {
                self::addPoints($post_owner_id, $ownerDelta);
            }
        }
        $result['score'] = self::getUserScore($voter_id);

        $result['cost'] = $cost;
        $result['refund'] = $refund;
        $result['owner_delta'] = $ownerDelta;
        $result['msg'] = $refund > 0 ? 'Rating applied. Refund: ' . $refund . ' points.' : 'Rating applied.';
        return $result;
    }

    /**
     * Handle comment vote spending and owner adjustment (up/down/meh).
     */
    public static function applyCommentVote($voter_id, $comment_owner_id, $vote) {

        $result = ['error' => 0, 'msg' => 'Karma disabled. No cost applied.', 'owner_delta' => 0, 'cost' => 0];

        if (!self::$enabled) {
            return $result;
        }
        $vote = strtolower($vote);
        $impact = 0;

        if ($vote === 'up') {
            $impact = 1;
        } elseif ($vote === 'down') {
            $impact = -1;
        } elseif ($vote === 'meh') {
            $impact = 0;
        } else {
            return ['error' => 1, 'msg' => 'Invalid vote type.'];
        }

        if ($comment_owner_id > 0 && $comment_owner_id === $voter_id) {
            return ['error' => 1, 'msg' => 'You cannot vote your own comment.'];
        }

        if (!self::canSpend($voter_id, self::COMMENT_VOTE_COST)) {
            return ['error' => 1, 'msg' => 'Insufficient karma points to vote.'];
        }

        if (!self::spendPoints($voter_id, self::COMMENT_VOTE_COST)) {
            return ['error' => 1, 'msg' => 'Insufficient karma points to vote.'];
        }


        if ($comment_owner_id > 0 && $comment_owner_id !== $voter_id && $impact !== 0) {
            if ($impact < 0) {
                self::applyLossWithLimit($comment_owner_id, abs($impact));
            } else {
                self::addPoints($comment_owner_id, $impact);
            }
        }

        $result['score'] = self::getUserScore($voter_id);
        $result['cost'] = self::COMMENT_VOTE_COST;
        $result['owner_delta'] = $impact;
        $result['msg'] = 'Vote applied.';
        
        return $result;
    }

    /**
     * Penalize a false report.
     * Example hook: moderator flags report as false → penalizeFalseReport($reporterId);
     */
    public static function penalizeFalseReport($user_id) {
        self::applyLossWithLimit($user_id, abs(self::PENALTY_FALSE_REPORT));
    }

    /**
     * Penalize flooding/excessive actions.
     * Example hook: rate limiter triggers → penalizeFlooding($userId);
     */
    public static function penalizeFlooding($user_id) {
        self::applyLossWithLimit($user_id, abs(self::PENALTY_FLOOD));
    }

    /**
     * Penalize detected self-voting.
     * Example hook: detection of self vote → penalizeSelfVoteDetected($userId);
     */
    public static function penalizeSelfVoteDetected($user_id) {
        self::applyLossWithLimit($user_id, abs(self::PENALTY_SELF_VOTE));
    }

    /**
     * Penalize when comment is removed by moderation
     */
    public static function penalizeCommentRemoved($user_id) {
        self::applyLossWithLimit($user_id, abs(self::PENALTY_COMMENT_REMOVED));
    }

    /**
     * Penalize spam content
     */
    public static function penalizeSpamContent($user_id) {
        self::applyLossWithLimit($user_id, abs(self::PENALTY_SPAM_CONTENT));
    }

    /**
     * Optional daily recovery to avoid user starvation. Caller should run once per day.
     */
    public static function applyMinimumDailyRecovery($user_id) {
        if (!self::$enabled || self::MINIMUM_DAILY_RECOVERY <= 0 || $user_id <= 0) {
            return;
        }
        $sessionKey = 'karma_recovered_' . $user_id;
        $today = self::todayKey();
        if (isset($_SESSION[$sessionKey]) && $_SESSION[$sessionKey] === $today) {
            return; // ya aplicado hoy en esta sesión
        }
        self::addPoints($user_id, self::MINIMUM_DAILY_RECOVERY);
        $_SESSION[$sessionKey] = $today;
    }


    public static function showUserScore() {
        if (!self::$enabled) {
            return;
        }
        $points = Login::getUserScore()??0;          
        //$html_users_core = '<span id="user_score" style="position:fixed; bottom:10px; right:10px; background-color:#f0f0f0; border:1px solid #ccc; padding:5px 10px; font-size:12px; z-index:1000;">'.$points.'</span>';
        echo $points>0
             ?'<span id="user_score" style="background-color:var(--red);color: white;padding: 1px 5px 1px 4px;font-size:0.7em;border-radius:16px;">'.$points.'</span>'
             :'';
    }


    public static function updateUserScore() {
        if (!self::$enabled || !isset($_SESSION['userid'])) {
            return;
        }
        $points = Login::getUserScore();          
        echo "<script>
            (function(){
                var el = document.getElementById('user_score');
                if (el) { el.innerText = '".intval($points)."'; }
            })();
        </script>";
    }

    /**
     * Recompensa por login diario (solo una vez al día, usando sesión)
     * No premia streaks, solo da 1 punto por día de actividad
     */
    public static function rewardDailyLogin($user_id) {
        if (!self::$enabled || $user_id <= 0) return;
        
        $sessionKey = 'karma_daily_login_' . $user_id;
        $today = date('Y-m-d');
        
        // Verificar si ya se recompensó hoy en esta sesión
        if (isset($_SESSION[$sessionKey]) && $_SESSION[$sessionKey] === $today) {
            return; // Ya recompensado hoy
        }
        
        // Dar puntos y marcar en sesión
        self::addPoints($user_id, self::DAILY_LOGIN);
        $_SESSION[$sessionKey] = $today;
    }

}
