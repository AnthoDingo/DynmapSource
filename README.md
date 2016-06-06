# Server Configuration

By default, Nginx and PHP5 allow POST 2M.
It's to small. Some Map.png are larger than 2M.

### Nginx
In file /etc/nginx/nginx.conf, add this line:

`
http {
...
client_max_body_size 20M;
...
}
`

### PHP
Modify your php.ini file :

`
memory_limit = 32M
upload_max_filesize = 20M
post_max_size = 20M
`

### Custom Size :
Change 20M by the size you want, ex: 30M or 1G