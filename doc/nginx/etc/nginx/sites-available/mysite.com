server {
    listen       80;
    server_name  mysite.com;

    root /path/to/ezpublish-community/web;

    # Legacy example
    # root /path/to/ezpublish-community/ezpublish_legacy;

    location / {
        include ez_params.d/ez_rewrite_params;

        # Additional Assetic rules for eZ Publish 5.1 / 2013.4 and higher.
        ## Don't forget to run php ezpublish/console assetic:dump --env=prod
        ## and make sure to comment these out in DEV environment.
        include ez_params.d/ez_prod_rewrite_params;

        location "/index.php" {
            include ez_params.d/ez_fastcgi_params;

            # FPM socket
            fastcgi_pass unix:/var/run/php5-fpm.sock;
            # FPM network
            #fastcgi_pass 127.0.0.1:9000;

            # Environment.
            # Possible values: "prod" and "dev" out-of-the-box, other values possible with proper configuration
            # Make sure to comment the "ez_params.d/ez_prod_rewrite_params" include above in dev.
            # Defaults to "prod" if omitted
            #fastcgi_param ENVIRONMENT dev;

            # Whether to use Symfony's ApcClassLoader.
            # Possible values: 0 or 1
            # Defaults to 0 if omitted
            #fastcgi_param USE_APC_CLASSLOADER 0

            # Prefix used when USE_APC_CLASSLOADER is set to 1.
            # Use a unique prefix in order to prevent cache key conflicts
            # with other applications also using APC.
            # Defaults to "ezpublish" if omitted
            #fastcgi_param APC_CLASSLOADER_PREFIX "ezpublish"

            # Whether to use debugging.
            # Possible values: 0 or 1
            # Defaults to 0 if omitted, unless ENVIRONMENT is set to: "dev"
            #fastcgi_param USE_DEBUGGING 0

            # Whether to use Symfony's HTTP Caching.
            # Disable it if you are using an external reverse proxy (e.g. Varnish)
            # Possible values: 0 or 1
            # Defaults to 1 if omitted, unless ENVIRONMENT is set to: "dev"
            #fastcgi_param USE_HTTP_CACHE 1

            # Defines the proxies to trust.
            # Separate entries by a comma
            # Example: "proxy1.example.com,proxy2.example.org"
            # By default, no trusted proxies are set
            #fastcgi_param TRUSTED_PROXIES "127.0.0.1"
        }
    }

    # Custom logs
    # access_log /home/user/ezpublish5/ezpublish-community/logs/httpd-access.log;
    # error_log  /home/user/ezpublish5/ezpublish-community/logs/httpd-error.log notice;

    include ez_params.d/ez_server_params;
}

