<?php
/*

File: class.ngram.php
License: BSD 3-Clause License <https://opensource.org/licenses/BSD-3-Clause>
Description: A class for comparing strings based on their n-gram similarity and for creating n-gram models from text. It also includes a method for generating shingles from text.

Author: https://www.phpclasses.org/browse/author/144301.html

Title: N-Gram Comparison and Shingling in PHP
Short Description: The NgramComparator class provides methods for comparing strings based on their n-gram similarity and for creating n-gram models from text. It also includes a method for generating shingles from text.

Long Description: The NgramComparator class is a PHP class that provides several methods for working with n-grams and shingles. N-grams are contiguous sequences of n items from a given sample of text, while shingles are overlapping sequences of words. The class includes the following methods:

- get_ngrams($text, $n): This method takes a string of text and an integer n as input and returns an array of n-grams. The method splits the input text into n-grams and returns an array of these n-grams.

- compare_strings_ngram_pct($string1, $string2, $n): This method takes two strings and an integer n as input and returns the percentage of matching n-grams between the two strings. The method splits the two input strings into n-grams and calculates the percentage of matching n-grams.

- compare_strings_ngram_max_size($string1, $string2): This method takes two strings as input and returns the maximum matching n-gram size between the two strings. The method splits the two input strings into n-grams of varying lengths and returns the size of the largest matching n-gram.

- get_shingles($text, $shingle_size): This method takes a string of text and an integer shingle_size as input and returns an array of shingles. The method splits the input text into shingles of the specified size and returns an array of these shingles.

- train_ngram_model($tokenized_text=[], $n=[]): This method takes an array of tokenized text and an integer n as input and returns an array of n-gram counts. The method loops through each sentence in the tokenized text and creates n-grams of length n. It then counts the frequency of each n-gram and returns an array of n-gram counts.

Use Cases:
Here are a few examples of how the NgramComparator class can be used:

- Comparing plagiarism between two documents: To check for plagiarism between two documents, you can use the compare_strings_ngram_pct method to get the percentage of matching n-grams between the two documents. If the percentage is above a certain threshold, you can flag the documents as potentially plagiarized.

- Finding the similarity between two strings: To find the similarity between two strings, you can use the compare_strings_ngram_max_size method to get the largest matching n-gram size between the two strings.

- Creating an n-gram model from text: To create an n-gram model from a corpus of text, you can use the train_ngram_model method. This method takes an array of tokenized text and an integer n as input and returns an array of n-gram counts. You can then use this n-gram model to perform various tasks such as text classification, language identification, and more.

Other Details:
- The NgramComparator class is written in PHP.
- The class can be used in various applications that require text similarity measures.
- The class is open-source and can be modified and extended as needed.

*/

class NgramComparator {
    public function get_ngrams($text, $n) {
        // split text into n-grams.
        $ngrams = [];
        for ($i = 0; $i < strlen($text) - $n + 1; $i++) {
            $ngrams[] = substr($text, $i, $n);
        }
        return $ngrams;
    }

    public function compare_strings_ngram_pct($string1, $string2, $n) {
        // compare two strings based on the percentage of matching n-grams
        // Split strings into n-grams
        $string1_ngrams = $this->get_ngrams($string1, $n);
        $string2_ngrams = $this->get_ngrams($string2, $n);
        // Find the number of matching n-grams
        $matching_ngrams = array_intersect($string1_ngrams, $string2_ngrams);
        // Calculate the percentage match
        $percentage_match = (count($matching_ngrams) / count($string1_ngrams)) * 100;
        return $percentage_match;
    }

    public function compare_strings_ngram_max_size($string1, $string2) {
        // compare two strings based on the maximum matching n-gram size
        // Split strings into n-grams of varying lengths
        $n = min(strlen($string1), strlen($string2));
        for ($i = $n; $i > 0; $i--) {
            $string1_ngrams = $this->get_ngrams($string1, $i);
            $string2_ngrams = $this->get_ngrams($string2, $i);
            // Find the number of matching n-grams
            $matching_ngrams = array_intersect($string1_ngrams, $string2_ngrams);
            if (count($matching_ngrams) > 0) {
                // Return the maximum matching n-gram size and break out of the loop
                return $i;
            }
        }
        // If no matching n-grams are found, return 0
        return 0;
    }

    public function get_shingles($text, $shingle_size) {
        $words = explode(' ', $text);
        $shingles = [];
        for ($i = 0; $i <= count($words) - $shingle_size; $i++) {
            $shingles[] = implode(' ', array_slice($words, $i, $shingle_size));
        }
        return $shingles;
    }

    public function train_ngram_model($tokenized_text=[], $n=[]) {
        // Create an n-gram model from tokenized text
        $ngram_counts = [];

        // Loop through each sentence in the tokenized text
        foreach ($tokenized_text as $sentence) {
            // Loop through each n-gram in the sentence
            for ($i = 0; $i < count($sentence) - $n + 1; $i++) {
                // Get the n-gram
                $ngram = implode(' ', array_slice($sentence, $i, $n));
                // If the n-gram already exists in the n-gram counts array, increment the count
                if (array_key_exists($ngram, $ngram_counts)) {
                    $ngram_counts[$ngram] += 1;
                }
                // If the n-gram doesn't exist in the n-gram counts array, add it with a count of 1
                else {
                    $ngram_counts[$ngram] = 1;
                }
            }
        }

        return $ngram_counts;
    }
}
