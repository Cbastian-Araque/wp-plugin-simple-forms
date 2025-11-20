# Simple Forms — Plugin de Formularios para WordPress

**Simple Forms** es un plugin ligero y modular que permite crear, gestionar y mostrar formularios personalizados dentro de WordPress.  
Diseñado con **Programación Orientada a Objetos (POO)** y **JavaScript moderno** empaquetado con **Vite**, este plugin busca ofrecer una base escalable y fácil de mantener para desarrolladores y usuarios.

---

## Características

- **Creación dinámica de formularios** con campos personalizados (texto, email, número, textarea, select, etc.)
- **Almacenamiento de formularios** en base de datos (estructura JSON)
- **Shortcodes** para mostrar formularios en cualquier página o entrada
- **Arquitectura modular y orientada a objetos**

## Desarrollo
- **Minifcar archivos /dist** ejecutar _npx vite build_ para crear y minificar los scripts y estilos

## Tecnologías
1. PHP8+
1. WordPress API (hooks, options, shortcodes, admin menus)
1. Vite (bundle, minify y ESModules)
1. JavaScript moderno (ES6+)
1. Programación Orientada a Objetos (POO)
1. Arquitectura modular y escalable

## Desinstalación limpia
**Al desinstalar el plugin:**
* Se eliminan todas las tablas personalizadas creadas en la base de datos.
* Se borran las opciones agregadas con add_option(). 
* No se eliminan los formularios publicados, para evitar pérdida de contenido accidental.

## Minificar JS
> npx vite build

## Minificar CSS
Se debe ejecutar manualmente, se debe agregar la ruta del archivo de entrada (con todos los archivos importados) y la ruta del archivo final (ya minificado), Ejemplo _sass assets/src/sass/style.scss assets/src/css/main.min.css --style=compressed_
> sass --input --output --style=compressed
