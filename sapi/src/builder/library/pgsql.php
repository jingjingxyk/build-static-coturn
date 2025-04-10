<?php

use SwooleCli\Library;
use SwooleCli\Preprocessor;

return function (Preprocessor $p) {
    $pgsql_prefix = PGSQL_PREFIX;
    $ldflags = $p->isMacos() ? '' : ' -static  ';
    $libs = $p->isMacos() ? '-lc++' : ' -lstdc++ ';
    $p->addLibrary(
        (new Library('pgsql'))
            ->withHomePage('https://www.postgresql.org/')
            ->withLicense('https://www.postgresql.org/about/licence/', Library::LICENSE_SPEC)
            ->withUrl('https://ftp.postgresql.org/pub/source/v16.3/postgresql-16.3.tar.gz')
            ->withManual('https://www.postgresql.org/docs/current/install-procedure.html#CONFIGURE-OPTIONS')
            ->withManual('https://www.postgresql.org/download/')
            ->withManual('https://git.postgresql.org/gitweb/?p=postgresql.git;a=summary')
            ->withManual('https://www.postgresql.org/docs/current/install-procedure.html#CONFIGURE-OPTIONS#:~:text=Client-only%20installation')
            ->withFileHash('md5', '8a58db4009e1a50106c5e1a8c4b03bed')
            ->withPrefix($pgsql_prefix)
            /*
                https://git.postgresql.org/gitweb/

                git://git.postgresql.org/git/postgresql.git
                https://git.postgresql.org/git/postgresql.git
                ssh://git@git.postgresql.org/postgresql.git
             */
            ->withBuildScript(
                <<<EOF
            test -d build_dir && rm -rf build_dir
            mkdir -p build_dir
            cd build_dir

            ../configure --help

            sed -i.backup "s/invokes exit\'; exit 1;/invokes exit\';/"  ../src/interfaces/libpq/Makefile

            sed -i.backup "278 s/^/# /"  ../src/Makefile.shlib
            sed -i.backup "402 s/^/# /"  ../src/Makefile.shlib

            PACKAGES="libssl libcrypto openssl zlib icu-uc icu-io icu-i18n readline libxml-2.0  libxslt libzstd liblz4"

            CPPFLAGS="$(pkg-config  --cflags-only-I --static \$PACKAGES )" \
            LDFLAGS="$(pkg-config   --libs-only-L   --static \$PACKAGES ) {$ldflags} " \
            LIBS="$(pkg-config      --libs-only-l   --static \$PACKAGES ) {$libs}  " \
            ../configure  \
            --prefix={$pgsql_prefix} \
            --enable-coverage=no \
            --with-openssl \
            --with-ssl=openssl  \
            --with-readline \
            --with-icu \
            --with-libxml  \
            --with-libxslt \
            --with-lz4 \
            --with-zstd \
            --without-ldap \
            --without-perl \
            --without-python \
            --without-pam \
            --without-ldap \
            --without-bonjour \
            --without-tcl

            make -C src/bin/pg_config install

            make -C src/include install

            make -C  src/common install

            # make -C  src/backend/port install
            make -C  src/port install

            # make -C  src/backend/libpq install
            make -C  src/interfaces/libpq install


            # make -C src/bin install
            # make -C src/include install
            # make -C src/common install
            # make -C src/port install
            # make -C src/interfaces install

            # make -C doc install

EOF
            )
            ->withScriptAfterInstall(
                <<<EOF
            rm -rf {$pgsql_prefix}/lib/*.so.*
            rm -rf {$pgsql_prefix}/lib/*.so
            rm -rf {$pgsql_prefix}/lib/*.dylib
            rm -rf {$pgsql_prefix}/lib/libpgcommon_shlib.a
            rm -rf {$pgsql_prefix}/lib/libpgport_shlib.a

EOF
            )
            ->withPkgName('libpq')
            ->withBinPath($pgsql_prefix . '/bin/')
            ->withDependentLibraries(
                'zlib',
                'icu',
                'libxml2',
                'openssl',
                'readline',
                'libxslt',
                'libzstd',
                'liblz4'
            )
    );
    $p->withExportVariable('LIBPQ_CFLAGS', '$(pkg-config  --cflags --static libpq)');
    $p->withExportVariable('LIBPQ_LIBS', '$(pkg-config    --libs   --static libpq)');
    $p->withExportVariable('PGSQL_CFLAGS', '$(pkg-config  --cflags --static  libpq)');
    $p->withExportVariable('PGSQL_LIBS', '$(pkg-config    --libs   --static  libpq)');
};

/*

    cd src/common && make -s -j$(nproc) all && make -s install && cd ../.. && \
    cd src/port && make -s -j$(nproc) all && make -s install && cd ../.. && \
    cd src/interfaces/libpq make -s -j$(nproc) all-static-lib && make -s install install-lib-static && \
    cd ../../bin/pg_config && make -j $(nproc) && make install && \

 */
