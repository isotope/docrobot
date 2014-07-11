<?php

namespace IsotopeDocRobot\Book;


class Book
{
    public function createBook()
    {
        $tarName = 'textest.tar';
        $mainFile = 'test.tex';

        $phar = new PharData($tarName);
        $phar->addFile($mainFile);
        $phar->addFile('test2.tex');
        $phar->compress(Phar::BZ2);

        /*
         * send file to http://latex.aslushnikov.com/data?target=' . $mainFile . '&force=true
        */


        // Clean up
        unlink($tarName);
        unlink($tarName . '.bz2');
    }
} 