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
* Secure when printing variables , as its filtered against most xss vulnerabilities.

Installation / Usage
--------------------

1. Install composer https://github.com/composer/composer
2. Create a composer.json inside your application folder:

    ``` composer require php-mohamed-nabil/style ```

Create a Style instance by passing it the folder(s) where your view files are located, and a cache folder. Render a template by calling the render method.

```php
use Style\Style;

$style = new Style('template/','template/temp/');

$style->render('page_sections',[]);
```

 Licence
-------

published under the MIT Licence.
