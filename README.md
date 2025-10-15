# ¡Bienvenido a Ecommerce!

Estamos encantados de que formes parte de nuestra comunidad. Con nuestra plataforma, podrás gestionar tu tienda en línea de manera eficiente y potenciar tu negocio digital.

## 📌 Tus datos de acceso
- **URL de acceso:** https://tudominio.com/admin 
- **Usuario:** admin@gmail.com  
- **Contraseña:** admin123 (puedes cambiarla desde la configuración)  

## 📖 Documentación y soporte
Para ayudarte a empezar, hemos preparado una documentación completa donde encontrarás guías paso a paso sobre cómo configurar y gestionar tu ecommerce:  


# 📦 Guía de Instalación

Sigue estos pasos para instalar y configurar correctamente la solución en tu servidor.

## 1️⃣ Subir archivos al servidor  
Ubica el contenido del archivo ZIP en el directorio de tu servidor donde se ejecutará la aplicación.

## 2️⃣ Instalar dependencias de Node.js  
Ejecuta el siguiente comando en la raíz del proyecto para instalar las dependencias de Node.js:  

```sh
npm install
```

## 3️⃣ Instalar dependencias de PHP (Composer)  
Para instalar los paquetes de Composer, ejecuta:  

```sh
composer install
```

Si estás en un servidor sin Composer global, usa:  

```sh
php composer.phar install
```

## 4️⃣ Restaurar la base de datos  
Ubica el archivo de la base de datos en el directorio `database/` y restáurala en tu gestor de bases de datos.

## 5️⃣ Configurar el entorno  
Copia el archivo de configuración de ejemplo y renómbralo como `.env`:  

```sh
cp .env.example .env
```

Luego, edita el archivo `.env` y ajusta los valores según tu configuración de base de datos y servidor.

Modifica las variables de entorno correspondientes a la conexión de la base de datos:

```sh
DB_CONNECTION=mysql
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

## 6️⃣ Generar la clave de la aplicación  
Ejecuta el siguiente comando para generar la clave de la aplicación:  

```sh
php artisan key:generate
```

## 7️⃣ Configurar las imágenes  
Para configurar las imágenes de tu sitio, genera el enlace simbólico:  

```sh
php artisan storage:link
```

Si necesitas crear un enlace simbólico manualmente, puedes usar el siguiente comando en Linux o macOS:  

```sh
ln -s storage/app/public public/storage
```

---

✅ ¡Listo! Ahora puedes proceder con la configuración final y el despliegue de la aplicación. 🚀
