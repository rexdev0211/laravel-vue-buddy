<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BuddyLinkService
{
    const STORAGE_FILE = 'buddyLinks';

    /** @var array */
    protected static $reservedNamesArray = null;

    protected static function getReservedNames(): array
    {
        $result = [];
        if (\Storage::exists(self::STORAGE_FILE)) {
            $fileContent = \Storage::get(self::STORAGE_FILE);
            $lines = preg_split('/[\r\n]+/', $fileContent);
            $result = array_map(function($value){
                return strtolower($value);
            }, $lines);
        }
        return $result;
    }

    public static function isReserved(string $buddyLinks): bool
    {
        if (empty(self::$reservedNamesArray)) {
            self::$reservedNamesArray = self::getReservedNames();
        }

        return in_array(
            strtolower($buddyLinks),
            self::$reservedNamesArray
        );
    }

    /**
     * Create a random BuddyLink according to this schema:
     * - 7 letters
     * - "consonant-vowel-con-vow-con-vow-con"
     * - eg: valeron, gomitas, zenabir
     *
     * @return string
     */
    protected static function getRandomBuddyLink(): string
    {
        $consonants = ['b','c','d','f','g','h','j','k','l','m','n','p','q','r','s','t','v','w','x','z'];
        $vowels = ['a','e','i','o','u','y'];
        $buddyLinkLetters = [];
        for ($i = 0; $i <= 3; $i++) {
            $buddyLinkLetters[] = Arr::random($consonants);
            if ($i <= 2) {
                $buddyLinkLetters[] = Arr::random($vowels);
            }
        }
        $buddyLink = implode('', $buddyLinkLetters);
        return $buddyLink;
    }

    /**
     *  Rules
     *  + unique (case insensitive)
     *  + minimum 2, up to 15 characters
     *  + users may select a minimum of 4 characters
     *  + only letters, numbers, underscore
     *  + users may select a minimum of 4 characters, 2-3 characters is by admin approval only
     *
     *  Steps
     *  + copy 1:1 if they match the requirements
     *  + truncate longer usernames to 15 char
     *  + replace - with _ (unless it would create a duplicate)
     *  + replace a space with _ (unless duplicate)
     *  + replace a . with _ (unless duplicate)
     *  + keep the letter cases for style reasons, but since the BuddyLink is NOT case sensitive, there must not be duplicates with upper and lowercase IDs
     *  - ignore deleted, suspended, ghosted accounts
     *
     * @param string $name
     * @param bool $forceRandom
     * @param bool $enableBlacklist
     *
     * @return string
     */
    public static function getComputedBuddyLink(string $name, bool $forceRandom = false, bool $enableBlacklist = false): string
    {
        $buddyLink = null;
        if (
            !empty($name)
            &&
            !$forceRandom
        ) {
            $buddyLink = Str::ascii($name, 'en');

            // Replace @ with the word 'at'
            $buddyLink = str_replace('@', '_at_', $buddyLink);

            // Remove all characters that are not the separator, letters, numbers, or whitespace.
            $buddyLink = preg_replace('![^_\-\.\pL\pN\s]+!u', '', $buddyLink);

            // Replace all separator characters and whitespace by a single separator
            $buddyLink = preg_replace(
                ['![_\s]+!u', '![-\s]+!u', '![\.\s]+!u', '![_\-.\s]{2,}!u'],
                ['_', '-', '.', '_'],
                $buddyLink
            );

            // Limit to 15 characters
            $buddyLink = Str::limit($buddyLink, 15, '');

            // Trim
            $buddyLink = trim($buddyLink, '_.-');

            if (
                strlen($buddyLink) < 4
                ||
                is_numeric($buddyLink)
            ) {
                $buddyLink = self::getRandomBuddyLink();
            }
        } else {
            $buddyLink = self::getRandomBuddyLink();
        }

        if ($enableBlacklist) {
            while (self::isReserved($buddyLink)) {
                $buddyLink = self::getRandomBuddyLink();
            }
        }

        return $buddyLink;
    }

}