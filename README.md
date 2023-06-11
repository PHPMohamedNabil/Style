# Style 

By Mohamed Nabil (https://github.com/PHPMohamedNabil/)!

[Style]
a tiny PHP Template Engine you can use for small projects or testing purposess.

it loads HTML template to separate the presentation from the logic.

Features
--------
* Simple compiling tags *{$variable}*, *{#constant}*, @include(), *{%loop%}*, *{%if%}*, *[* comment *]*, *{noparse}*, *{%function%}*
* Very Easy template Compiling as one class called view just loads the full template and compiling it.
* [new feature] (Hardcompiling Templates) to send data to other template file so that be injected into other template in every page load.
* Easy to inject a new experissions fell free to add as many as you want.
* Secure, sandbox with blacklist.


Installation / Usage
--------------------

1. Install composer https://github.com/composer/composer
2. Create a composer.json inside your application folder:

    ``` composer require php-mohamed-nabil/style ```


 Licence
-------

published under the MIT Licence.
