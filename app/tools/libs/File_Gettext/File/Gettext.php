<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * File::Gettext
 *
 * PHP versions 4 and 5
 *
 * @category  FileFormats
 * @package   File_Gettext
 * @author    Michael Wallner <mike@php.net>
 * @copyright 2004-2005 Michael Wallner
 * @license   BSD, revised
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/File_Gettext
 */

/**
 * Use PHPs builtin error messages
 */
ini_set('track_errors', true);

/**
 * File_Gettext
 *
 * GNU gettext file reader and writer.
 *
 * #################################################################
 * # All protected members of this class are public in its childs. #
 * #################################################################
 *
 * @category  FileFormats
 * @package   File_Gettext
 * @author    Michael Wallner <mike@php.net>
 * @copyright 2004-2005 Michael Wallner
 * @license   BSD, revised
 * @link      http://pear.php.net/package/File_Gettext
 */
class File_Gettext
{
    /**
     * strings
     *
     * associative array with all [msgid => msgstr] entries
     *
     * @access  protected
     * @var     array
     */
    var $strings = array();

    /**
     * meta
     *
     * associative array containing meta
     * information like project name or content type
     *
     * @access  protected
     * @var     array
     */
    var $meta = array();

    /**
     * file path
     *
     * @access  protected
     * @var     string
     */
    var $file = '';

    /**
     * Factory
     *
     * @param string $format MO or PO
     * @param string $file path to GNU gettext file
     *
     * @static
     * @access  public
     * @return  object  Returns File_Gettext_PO or File_Gettext_MO on success
     *                  or PEAR_Error on failure.
     */
    function &factory($format, $file = '')
    {
        $format = strToUpper($format);
        $class = 'File_Gettext_' . $format;
        $obref = &new $class($file);

        return $obref;
    }

    /**
     * poFile2moFile
     *
     * That's a simple fake of the 'msgfmt' console command.  It reads the
     * contents of a GNU PO file and saves them to a GNU MO file.
     *
     * @param string $pofile path to GNU PO file
     * @param string $mofile path to GNU MO file
     *
     * @static
     * @access  public
     * @return  mixed   Returns true on success or PEAR_Error on failure.
     */
    function poFile2moFile($pofile, $mofile)
    {
        $PO = &new File_Gettext_PO($pofile);
        if (true !== ($e = $PO->load())) {
            return $e;
        }

        $MO = &$PO->toMO();
        if (true !== ($e = $MO->save($mofile))) {
            return $e;
        }
        unset($PO, $MO);

        return true;
    }

    /**
     * toMO
     *
     * @access  protected
     * @return  object  File_Gettext_MO
     */
    function &toMO()
    {
        $MO = &new File_Gettext_MO;
        $MO->fromArray($this->toArray());

        return $MO;
    }

    /**
     * fromArray
     *
     * Assigns meta info and strings from an array of a structure like that:
     * <code>
     *   array(
     *       'meta' => array(
     *           'Content-Type'      => 'text/plain; charset=iso-8859-1',
     *           'Last-Translator'   => 'Michael Wallner <mike@iworks.at>',
     *           'PO-Revision-Date'  => date('Y-m-d H:iO'),
     *           'Language-Team'     => 'German <mail@example.com>',
     *       ),
     *       'strings' => array(
     *           'All rights reserved'   => 'Alle Rechte vorbehalten',
     *           'Welcome'               => 'Willkommen',
     *           // ...
     *       )
     *   )
     * </code>
     *
     * @param array $array Array to populate from.
     *
     * @see     toArray()
     * @access  protected
     * @return  bool
     */
    function fromArray($array)
    {
        if (!array_key_exists('strings', $array)) {
            if (count($array) != 2) {
                return false;
            } else {
                list($this->meta, $this->strings) = $array;
            }
        } else {
            $this->meta = @$array['meta'];
            $this->strings = @$array['strings'];
        }

        return true;
    }

    /**
     * toArray
     *
     * Returns meta info and strings as an array of a structure like that:
     * <code>
     *   array(
     *       'meta' => array(
     *           'Content-Type'      => 'text/plain; charset=iso-8859-1',
     *           'Last-Translator'   => 'Michael Wallner <mike@iworks.at>',
     *           'PO-Revision-Date'  => '2004-07-21 17:03+0200',
     *           'Language-Team'     => 'German <mail@example.com>',
     *       ),
     *       'strings' => array(
     *           'All rights reserved'   => 'Alle Rechte vorbehalten',
     *           'Welcome'               => 'Willkommen',
     *           // ...
     *       )
     *   )
     * </code>
     *
     * @see     fromArray()
     * @access  protected
     * @return  array
     */
    function toArray()
    {
        return array('meta' => $this->meta, 'strings' => $this->strings);
    }

    /**
     * prepare
     *
     * @param string $string String to prepare
     * @param bool $reverse Reverse ?
     *
     * @static
     * @access  protected
     * @return  string
     */
    function prepare($string, $reverse = false)
    {
        if ($reverse) {
            $smap = array('\\', '"', "\n", "\t", "\r");
            $rmap = array('\\\\', '\\"', '\\n"' . "\n" . '"', '\\t', '\\r');

            return (string)str_replace($smap, $rmap, $string);
        } else {
            $smap = array('/"\s+"/', '/\\\\n/', '/\\\\r/', '/\\\\t/', '/\\\\"/', '/\\\\\\\\/');
            $rmap = array('', "\n", "\r", "\t", '"', '\\');

            return (string)preg_replace($smap, $rmap, $string);
        }
    }

    /**
     * meta2array
     *
     * @param string $meta Meta data to turn into an array
     *
     * @static
     * @access  public
     * @return  array
     */
    function meta2array($meta)
    {
        $array = array();
        foreach (explode("\n", $meta) as $info) {
            if ($info = trim($info)) {
                list($key, $value) = explode(':', $info, 2);
                $array[trim($key)] = trim($value);
            }
        }

        return $array;
    }

    /**
     * toPO
     *
     * @access  protected
     * @return  object      File_Gettext_PO
     */
    function &toPO()
    {
        $PO = &new File_Gettext_PO;
        $PO->fromArray($this->toArray());

        return $PO;
    }
}