<?php

use SwooleCli\Library;
use SwooleCli\Preprocessor;

return function (Preprocessor $p) {
    $liblzma_prefix = LIBLZMA_PREFIX;
    $libiconv_prefix = ICONV_PREFIX;
    $p->addLibrary(
        (new Library('liblzma'))
            ->withHomePage('https://tukaani.org/xz/')
            ->withLicense('https://github.com/tukaani-project/xz/blob/master/COPYING.GPLv3', Library::LICENSE_LGPL)
            //->withUrl('https://github.com/tukaani-project/xz/releases/download/v5.4.1/xz-5.4.1.tar.gz')
            ->withUrl('https://sourceforge.net/projects/lzmautils/files/xz-5.4.1.tar.gz')
            ->withFile('xz-5.4.1.tar.gz')
            ->withMd5sum('7e7454778b4cfae238a7660521b29b38')
            ->withPrefix($liblzma_prefix)
            ->withConfigure(
                <<<EOF
                ./configure --help
                ./configure \
                --prefix={$liblzma_prefix} \
                --enable-static=yes  \
                --enable-shared=no \
                --with-libiconv-prefix={$libiconv_prefix} \
                --without-libintl-prefix \
                --disable-doc
EOF
            )
            /*
            ->withScriptAfterInstall(
                <<<EOF
            cp -f  {$liblzma_prefix}/lib/pkgconfig/liblzma.pc {$liblzma_prefix}/lib/pkgconfig/lzma.pc
            cp -f  {$liblzma_prefix}/lib/liblzma.a {$liblzma_prefix}/lib/lzma.a
EOF
            )
            */
            ->withPkgName('liblzma')
            //->withBinPath($liblzma_prefix . '/bin/')
            ->withDependentLibraries('libiconv')
    );
};
