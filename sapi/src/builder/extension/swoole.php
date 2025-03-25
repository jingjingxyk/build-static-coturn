<?php

use SwooleCli\Preprocessor;
use SwooleCli\Extension;

return function (Preprocessor $p) {
    $swoole_tag = 'v6.0.1';
    $file = "swoole-{$swoole_tag}.tar.gz";

    $url = "https://github.com/swoole/swoole-src/archive/refs/tags/{$swoole_tag}.tar.gz";

    $options = [];

    if ($p->getBuildType() === 'debug') {
        $options[] = ' --enable-debug ';
        $options[] = ' --enable-debug-log ';
        $options[] = ' --enable-swoole-coro-time  ';
    }

    //call_user_func_array([$ext, 'withDependentLibraries'], $dependentLibraries);
    //call_user_func_array([$ext, 'withDependentExtensions'], $dependentExtensions);

    $dependentLibraries = ['curl', 'openssl', 'cares', 'zlib', 'brotli', 'nghttp2', 'sqlite3', 'unix_odbc', 'pgsql', 'libzstd'];
    $dependentExtensions = ['curl', 'openssl', 'sockets', 'mysqlnd', 'pdo'];

    $options[] = '--enable-swoole';
    $options[] = '--enable-sockets';
    $options[] = '--enable-mysqlnd';
    $options[] = '--enable-swoole-curl';
    $options[] = '--enable-cares';
    $options[] = '--with-brotli-dir=' . BROTLI_PREFIX;
    $options[] = '--with-nghttp2-dir=' . NGHTTP2_PREFIX;
    $options[] = '--enable-swoole-pgsql';
    $options[] = '--enable-swoole-sqlite';
    $options[] = '--with-swoole-odbc=unixODBC,' . UNIX_ODBC_PREFIX;
    $options[] = '--enable-swoole-thread';
    $options[] = '--enable-brotli';
    $options[] = '--enable-zstd';
    $options[] = '--enable-zts';
    $options[] = '--disable-opcache-jit';

    if ($p->isLinux() && $p->getInputOption('with-iouring')) {
        $options[] = '--enable-iouring';
        $dependentLibraries[] = 'liburing';
        $p->withExportVariable('URING_CFLAGS', '$(pkg-config  --cflags --static  liburing)');
        $p->withExportVariable('URING_LIBS', '$(pkg-config    --libs   --static  liburing)');
    }


    $p->addExtension((new Extension('swoole'))
        ->withHomePage('https://github.com/swoole/swoole-src')
        ->withLicense('https://github.com/swoole/swoole-src/blob/master/LICENSE', Extension::LICENSE_APACHE2)
        ->withManual('https://wiki.swoole.com/#/')
        ->withFile($file)
        ->withDownloadScript(
            'swoole-src',
            <<<EOF
            git clone -b {$swoole_tag} --depth=1 https://github.com/swoole/swoole-src.git
EOF
        )
        ->withOptions(implode(' ', $options))
        ->withBuildCached(false)
        ->withDependentLibraries(...$dependentLibraries)
        ->withDependentExtensions(...$dependentExtensions));

    $p->withBeforeConfigureScript('swoole', function (Preprocessor $p) {
        $cmd = '';
        if ($p->isMacos()) {
            $workDir = $p->getWorkDir();
            $cmd = <<<EOF
        cd {$workDir}/
        sed -i '' 's/pthread_barrier_init/pthread_barrier_init_x_fake/' ext/swoole/config.m4
EOF;
        }
        return $cmd;

    });
    if ($p->isMacos()) {
        # 测试 macos 专有特性
        # 定义 _GNU_SOURCE 会隐式启用 _POSIX_C_SOURCE=200112L 和 _XOPEN_SOURCE=600
        # export CFLAGS="$CFLAGS  " # -D_DARWIN_C_SOURCE=1 -D_XOPEN_SOURCE=700  -D_GNU_SOURCE  -D_POSIX_C_SOURCE=200809L
        # export LIBS="-Wl,--start-group -pthread  -Wl,--end-group"
        # export LIBS="-Wl,--whole-archive -pthread -Wl,--no-whole-archive "

        # 新版macos getdtablesize 函数缺失
        # sed -i '' 's/getdtablesize();/sysconf(_SC_OPEN_MAX);/' ext/standard/php_fopen_wrapper.c
        $libc = $p->isMacos() ? '-lc++' : '-lstdc++';

        # cd /Applications/Xcode.app/Contents/Developer/Platforms/MacOSX.platform/Developer/SDKs/MacOSX.sdk/usr/include/sys/_pthread
        # 或者
        # cd /Library/Developer/CommandLineTools/SDKs/MacOSX13.sdk/usr/include/sys/_pthread
        # grep -r 'pthread_barrier_init' .
        # grep -r 'pthread_barrier_t' .
    }
    $p->withVariable('LIBS', '$LIBS ' . ($p->isMacos() ? '-lc++' : '-lstdc++'));
    $p->withExportVariable('CARES_CFLAGS', '$(pkg-config  --cflags --static  libcares)');
    $p->withExportVariable('CARES_LIBS', '$(pkg-config    --libs   --static  libcares)');


    $p->withExportVariable('ZSTD_CFLAGS', '$(pkg-config  --cflags --static  libzstd)');
    $p->withExportVariable('ZSTD_LIBS', '$(pkg-config    --libs   --static  libzstd)');
};
