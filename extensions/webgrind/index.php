<?php
/**
 * @author Jacob Oettinger
 * @author Joakim Nygård
 */

require './library/common.php';


switch(get('op')) {
    case 'fileviewer':
        $file = get('file');
        $line = get('line');

        if ($file && $file != '') {
            $message = '';
            if (!file_exists($file)) {
                $message = $file.' does not exist.';
            } elseif (!is_readable($file)) {
                $message = $file.' is not readable.';
            } elseif (is_dir($file)) {
                $message = $file.' is a directory.';
            }
        } else {
            $message = 'No file to view';
        }
        require 'templates/fileviewer.phtml';
        break;

    case 'function_graph':
        $dataFile = get('dataFile');
        $showFraction = 100 - intval(get('showFraction') * 100);
        
        if ($dataFile == '0') {
            $files = Webgrind_FileHandler::getInstance()->getTraceList(1);
            $dataFile = $files[0]['filename'];
        }

        if (!is_executable(Webgrind::$config->pythonExecutable)) {
            die("can't find python interpreter");
        }

        if (!is_executable(Webgrind::$config->dotExecutable)) {
            die("can't find drawing tool");
        }

        $filename = Webgrind::$config->storageDir.'/'.$dataFile.'-'.$showFraction.Webgrind::$config->preprocessedSuffix.'.png';
        if (!file_exists($filename)) {
            if (!createGraph(
                Webgrind::$config->xdebugOutputDir.'/'.$dataFile,
                $filename,
                $showFraction
            )) {
                die("can't generate graph");
            }
        }

        header("Content-Type: image/png");
        readfile($filename);
        break;

    default:
        $welcome = '';
        $storageDir = Webgrind::$config->storageDir;
        $xdebugOutputDir = Webgrind::$config->xdebugOutputDir;

        if (!file_exists($storageDir) || !is_writable($storageDir)) {
            $welcome .= "Webgrind storageDir ${storageDir} does not exist or is not writeable.<br>";
        }
        if (!file_exists($xdebugOutputDir) || !is_readable($xdebugOutputDir)) {
            $welcome .= "Webgrind profilerDir ${xdebugOutputDir} does not exist or is not readable.<br>";
        }

        if ($welcome == '') {
            $welcome = "Select a cachegrind file above<br>(looking in <code>${xdebugOutputDir}</code> for files matching <code>".
                Webgrind::$config->xdebugOutputFormat.'</code>)';
        }
        require 'templates/index.phtml';
}
