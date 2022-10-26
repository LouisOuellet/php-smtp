![GitHub repo logo](/dist/img/logo.png)

# phpSMTP
![License](https://img.shields.io/github/license/LouisOuellet/php-smtp?style=for-the-badge)
![GitHub repo size](https://img.shields.io/github/repo-size/LouisOuellet/php-smtp?style=for-the-badge&logo=github)
![GitHub top language](https://img.shields.io/github/languages/top/LouisOuellet/php-smtp?style=for-the-badge)
![Version](https://img.shields.io/github/v/release/LouisOuellet/php-smtp?label=Version&style=for-the-badge)

## Features
 - SMTP Authentication
 - Support for Mail Templates

## Why you might need it
If you are looking for an easy way to authenticate users against a SMTP server or if you are looking to easily setup beautiful emails. This PHP Class is for you.

## Can I use this?
Sure!

## License
This software is distributed under the [GNU General Public License v3.0](https://www.gnu.org/licenses/gpl-3.0.en.html) license. Please read [LICENSE](LICENSE) for information on the software availability and distribution.

## Localization
This PHP Class supports languages by giving the ability to customize all included text field.

### Updating text fields

```php
<?php

//Import SMTP class into the global namespace
//These must be at the top of your script, not inside a function
use LaswitchTech\SMTP\phpSMTP;

//Load Composer's autoloader
require 'vendor/autoload.php';

$SMTP = new phpSMTP();

$SMTP->setTEXT([
  "Sincerely" => "Sincerely",
  "TM and copyright" => "TM and copyright",
  "All Rights Reserved" => "All Rights Reserved",
  "Privacy Policy" => "Privacy Policy",
  "Support" => "Support",
  "This message was sent to you from an email address that does not accept incoming messages" => "This message was sent to you from an email address that does not accept incoming messages",
  "Any replies to this message will not be read. If you have questions, please visit" => "Any replies to this message will not be read. If you have questions, please visit",
]);
```

## Requirements
PHP >= 5.5.0

## Security
Please disclose any vulnerabilities found responsibly – report security issues to the maintainers privately.

## Installation
Using Composer:
```sh
composer require laswitchtech/php-smtp
```

## Some Examples

### Sending an email using the default template

```php
<?php

//Import SMTP class into the global namespace
//These must be at the top of your script, not inside a function
use LaswitchTech\SMTP\phpSMTP;

//Load Composer's autoloader
require 'vendor/autoload.php';

$SMTP = new phpSMTP();

$SMTP->connect([
  "username" => "username@domain.com",
  "password" => "*******************",
  "host" => "mail.domain.com",
  "port" => "465",
  "encryption" => "ssl",
]);

if($SMTP->isConnected()){
  echo "Connection Established!\n";
  //The send method accepts an array to update the VAR property
  if($SMTP->send([
    "TO" => "username@domain.com",
    "SUBJECT" => "Lorem",
    "TITLE" => "Lorem Ipsum",
    "MESSAGE" => "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.",
  ])){ echo "Message Sent!\n"; }
}
```

### Updating variables

```php
<?php

//Import SMTP class into the global namespace
//These must be at the top of your script, not inside a function
use LaswitchTech\SMTP\phpSMTP;

//Load Composer's autoloader
require 'vendor/autoload.php';

$SMTP = new phpSMTP();

$SMTP->setVAR([
  "BRAND" => "phpSMTP",
  "LOGO" => "https://github.com/LouisOuellet/php-smtp/raw/stable/dist/img/logo.png",
  "FROM" => null,
  "TO" => null,
  "CC" => null,
  "BCC" => null,
  "REPLY-TO" => null,
  "SUBJECT" => "phpSMTP - Subject",
  "TITLE" => "phpSMTP - Title",
  "MESSAGE" => "phpSMTP - Message",
  "COPYRIGHT" => null,
  "TRADEMARK" => "https://domain.com/trademark",
  "POLICY" => "https://domain.com/policy",
  "SUPPORT" => "https://domain.com/support",
  "CONTACT" => "https://domain.com/contact",
]);
```

### Using a different template
There is 2 templates that are currently available. The default one is html.

```php
<?php

//Import SMTP class into the global namespace
//These must be at the top of your script, not inside a function
use LaswitchTech\SMTP\phpSMTP;

//Load Composer's autoloader
require 'vendor/autoload.php';

$SMTP = new phpSMTP();

//Provide a template file to use
$SMTP->setTemplate(dirname(__FILE__).'/templates/default.txt');
```

### Creating templates
You can create your own template using HTML or TEXT. The class uses tags like this ```[TEXT-Sincerely]``` or ```[VAR-LOGO]``` to insert the proper text field or variable content. Any of the text fields or variables can be used.

To create your own template, I suggest you look into the [templates](src/templates) directory.
