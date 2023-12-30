# static-coturn

构建静态 coturn

## 构建命令

>
复用 [jingjingxyk/swoole-cli `new_dev`分支](https://github.com/jingjingxyk/swoole-cli/tree/new_dev)
的 静态库构建流程

```bash

    git clone -b new_dev https://github.com/jingjingxyk/swoole-cli/
    cd swoole-cli
    php prepare.php +coturn
    bash make-install-deps.sh
    bash make.sh all-library
    bash make.sh config

```

## coturn 源码构建参考

    https://github.com/coturn/coturn.git
