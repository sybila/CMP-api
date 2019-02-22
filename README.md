# Installation
## OAuth
public/private key pair needs to be generated as well as encryption key
##### Generating public/private key pair
Use the following commands to create private/public key pair
```bash
openssl genrsa -out oauth2.priv 2048
openssl rsa -in oauth2.priv -pubout -out oauth2.pub
```
then set path to these files inside `settings.local.php`

##### Generating encryption key
`composer update` needs to be run before this step at least once
```bash
vendor/bin/generate-defuse-key
```
then copy and paste the generated string into `settings.local.php`
