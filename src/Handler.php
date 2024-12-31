<?php

namespace FireCore\IniWriter;

class Handler
{

    private static $file;
    private static $data = [];
    private static $hasSections;
    private static $continue = false;



    public static function open($file,$hasSections=true):void
    {
        self::$file = $file;
        self::$hasSections = $hasSections;
        self::$continue = true;
        self::parseIni();
    }

    private static function parseIni():void
    {
        if (file_exists(self::$file)) {
            self::$data = parse_ini_file(self::$file, self::$hasSections);
        } else {
            touch(self::$file);
        }
    }

    public static function set($section,$key,$value,$once=false):void
    {
        if(self::$continue)
        {
            if(self::$hasSections === true)
            {
                self::$data[$section][$key] = $value;
            }
            else
            {
                self::$data[$key] = $value;
            }
        }
        else
        {
            trigger_error("Ini File has not been loaded");
        }
    }

    public  static function get($section,$key):mixed
    {
        if(self::$hasSections === true)
        {
            return self::$data[$section][$key];
        }
        else
        {
            return self::$data[$key];
        }
    }

    private  static function writeFile($content):bool
    {
        return file_put_contents(self::$file, $content) !== false;
    }

    private static function getType($value):mixed
    {
        return (is_numeric($value) ? $value : '"' . addslashes($value) . '"');
    }

    public static  function countSection($section):int
    {
        return count(self::$data[$section]);
    }

    public static function remove($section,...$args)
    {
        if(self::$hasSections === true)
        {
            // Count Arguments
            if(count($args) > 0)
            {
                // Loop the Arguments
                foreach($args as $key)
                {
                    // Check if the exist in the array section
                    if(array_key_exists($key,self::$data[$section]))
                    {
                        // Unset the key
                        unset(self::$data[$section][$key]);
                    }
                    else
                    {
                        // If failed trigger an error.
                        trigger_error($key . " does not exist in " . $section);
                    }
                }
                //Delete section if no keys are left
                if(count(self::$data[$section]) === 0)
                {
                    unset(self::$data[$section]);
                }
            }
            else
            {
                // Delete the section no keys are passed
                unset(self::$data[$section]);
            }
        }
        else
        {
            // Unset the key if no section is set
                unset(self::$data[$section]);
        }
    }


    public static function save() :void{
        $content = '';
        if(self::$hasSections)
        {  
            foreach(self::$data as $section => $values)
            {
                $content .= '[' . $section . ']' . PHP_EOL;
                foreach($values as $key => $value)
                {
                    $content .= $key . ' = ' . self::getType($value) . PHP_EOL;
                }
            } 
             $content .= PHP_EOL;
        }
        else
        {
            foreach(self::$data as $key => $value)
            {
                $content .= $key . ' = ' . self::getType($value) . PHP_EOL;
            }
        }
        self::writeFile($content);
    }

}
