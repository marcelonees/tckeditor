# tckeditor

Use um editor WYSIWYG com mais recursos que o THtmlEditor

Segundo o fabricante:

> The most powerful Open Source rich text editor with a modular
> & modern architecture and 200+ features. Rock solid and fully customizable.

Site do fabricante [CkEditor](https://github.com/ckeditor/).

Instalação:

```bash
composer config repositories.tckeditor vcs https://github.com/marcelonees/tckeditor
composer require marcelonees/tckeditor @dev
composer require marcelonees/tckeditor dev-main
```

Uso:

```php
use MarceloNees\TCkEditorAdianti\TCkEditor;
$editor = new TCkEditor('editor');
```

# Este projeto está em estágio "embrionário".

## !!!!Não recomendado para produção!!!!
