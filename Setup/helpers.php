<?php


if (!function_exists('array_keys_exists')) {
    function array_keys_exists(array $keys, array $arr)
    {
        return !array_diff_key(array_flip($keys), $arr);
    }
}

if (!function_exists('prettyUtcDate')) {
    /**
     * Given a string with the date and time in UTC, returns a pretty string in the
     * configured language, format and timezone in WordPress' options.
     *
     * More info: https://wordpress.stackexchange.com/questions/94755/converting-timestamps-to-local-time-with-date-l18n/339190#339190
     *
     * @param string $utc_date_and_time
     *      e.g: "2019-05-30 18:06:01"
     *      This argument must be in UTC.
     * @return string
     *      e.g: "Maggio 30, 2019 10:06 am"
     *      This returns a pretty datetime string in the correct language and
     *      following the admin's settings.
     */
    function prettyUtcDate(string $utc_date): string
    {
        if (! preg_match('/^\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d$/', $utc_date)) {
            /* I have not tested other formats, so only this one allowed. */
            throw new InvalidArgumentException("Expected argument to be in YYYY-MM-DD hh:mm:ss format");
        }

        $date_in_local_timezone = get_date_from_gmt($utc_date);

        /* $date_in_local_timezone is now something like "2019-05-30 10:06:01"
        * in the timezone of get_option( 'timezone_string' ), configured in
        * WordPress' general settings in the backend user interface.
        */

        /* Unfortunately, we can't just pass this to WordPress' date_i18n, as that
        * expects the second argument to be the number of seconds since 1/Jan/1970
        * 00:00:00 in the timezone of get_option( 'timezone_string' ), which is not the
        * same as a UNIX epoch timestamp, which is the number of seconds since
        * 1/Jan/1970 00:00:00 GMT. */
        $seconds_since_local_1_jan_1970 =
            (new DateTime($date_in_local_timezone, new DateTimeZone('UTC')))
            ->getTimestamp();
        // e.g: 1559210761

        /* Administrators can set a preferred date format and a preferred time
        * format in WordPress' general settings in the backend user interface, we
        * need to retrieve that. */
        $settings_format = get_option('date_format') . ' '. get_option('time_format');
        // $settings_format is in this example "F j, Y g:i a"

        /* In this example, the installation of WordPress has been set to Italian,
        * and the final result is "Maggio 30, 2019 10:06 am" */
        return date_i18n($settings_format, $seconds_since_local_1_jan_1970);
    }
}


if (!function_exists('camelCase')) {
    function camelCase($string, $dontStrip = [])
    {
        /*
        * This will take any dash or underscore turn it into a space, run ucwords against
        * it so it capitalizes the first letter in all words separated by a space then it
        * turns and deletes all spaces.
        */
        return lcfirst(str_replace(' ', '', ucwords(preg_replace('/^a-z0-9'.implode('', $dontStrip).']+/', ' ', $string))));
    }
}
