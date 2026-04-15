<div class="inner">

    <h1><?=ucwords(MODULE)?></h1>

    <?php
    
    // echo ip_as_integer();

    // URL segments is $_ARGS[0], $_ARGS[1], $_ARGS[2] ... etc.
    //Vars::debug_var($_ARGS);

    //// Scaffold classes
    //// Initialize Scaffold engine
    Table::init();
    //// Show a existing table
    Table::show_tabs('', ['POST_COMMENTS'=>'Comments', 'POST_VOTES'=>'Votes', 'POST_RATINGS'=>'Ratings', 'CLI_USER_TRANSACTIONS' =>'Transactions']);

    
    ?>
 
   

</div>
