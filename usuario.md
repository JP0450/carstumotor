# Guía de usuario y presentación de la demo

## 1. Qué contiene la demostración

La demo funciona con una base SQLite local independiente: `database/presentation.sqlite`. No utiliza ni modifica la base indicada en `.env`.

El seeder agrega datos ficticios identificados como `DEMO` para mostrar:

- Cuatro cuentas con roles de gerencia, contabilidad, negocios internacionales y cliente.
- Ocho vehículos con ilustraciones locales livianas.
- Inventario, fichas internacionales, documentos PDF de ejemplo, citas e importaciones.
- Ingresos, egresos, impuestos, cuentas clasificadas por cobrar/pagar y un egreso de transporte asociado al BMW.
- Una venta de demostración asociada al Kia Sportage y su costo de venta.

Los registros demo se pueden volver a sembrar: se actualizan por sus identificadores demo y no se borran registros ajenos a la demo.

## 2. Iniciar la aplicación

1. Abre PowerShell en la carpeta raíz del proyecto.
2. Ejecuta:

   ```powershell
   powershell -ExecutionPolicy Bypass -File .\scripts\run-demo.ps1
   ```

3. Espera a que aparezca `Server running` y deja esa ventana abierta mientras presentas.
4. Abre [http://127.0.0.1:8001](http://127.0.0.1:8001).

El script prepara la base aislada, aplica las migraciones, actualiza los registros demo e inicia el servidor. No cierres la terminal durante la presentación.

### Acceso

Usa una de estas cuentas:

| Perfil | Correo |
|---|---|
| Gerencia | `demo.jefe@carstumotor.test` |
| Contabilidad | `demo.contador@carstumotor.test` |
| Negocios internacionales | `demo.internacional@carstumotor.test` |
| Cliente | `demo.cliente@carstumotor.test` |

La terminal muestra una contraseña temporal común para las cuatro cuentas. Cada vez que inicies el script, se genera otra; consulta la contraseña de la ejecución actual.

## 3. Recorrido sugerido para presentar

1. **Dashboard gerencial:** abre `/admin/dashboard/gerencial`. Deja el periodo en `Mes actual` y la moneda en `COP` para ver las cifras de demostración.
2. **Finanzas y contabilidad:** abre `/finanzas`. Busca el egreso de transporte vinculado al BMW y despliega `Registrar movimiento financiero` para enseñar el formulario y su vista previa.
3. **Catálogo:** abre `/catalogo` para recorrer los vehículos y sus ilustraciones.
4. **Negocios internacionales:** abre `/internacional/vehiculos` para mostrar las fichas y documentos.
5. **Informes:** desde el dashboard puedes exportar el resumen a PDF o CSV para Excel.

Todos los roles autenticados pueden abrir estas pantallas de demostración.

## 4. Consultar y registrar movimientos

En Finanzas, despliega el formulario y completa:

- Tipo: ingreso, gasto, impuesto o costo de venta.
- Valor y moneda: COP, USD o EUR.
- Categoría, fecha y descripción.
- Vehículo asociado, si el movimiento corresponde a uno.

Pulsa **Registrar movimiento**. El nuevo registro aparecerá en la tabla. Usa **Editar** para cambiarlo o **Eliminar** para retirarlo.

Para alimentar los importes del dashboard, selecciona una moneda y registra los movimientos en esa misma moneda. El dashboard no convierte divisas ni mezcla COP, USD y EUR. Los movimientos antiguos sin moneda permanecen sin moneda y no se suman a los importes por divisa.

## 5. Cómo interpretar las cifras

- Los importes y operaciones marcados `DEMO` son ficticios, solo para presentación; no son información contable real.
- Los ingresos y egresos del dashboard se filtran por periodo y moneda.
- La utilidad mostrada es ingresos menos egresos registrados en esa moneda; es una referencia de demostración, no un cierre contable.
- La venta demo se identifica por un movimiento de ingreso categorizado como `Venta de vehículo` y asociado a un vehículo.
- Los importes de cuentas por cobrar/pagar son los montos de registros clasificados con esos tipos. El sistema todavía no guarda abonos, saldos pendientes ni fechas de vencimiento, por lo que no representan saldos reales.
- El margen promedio del catálogo se estima con precio de venta y costo de empresa; no representa rentabilidad realizada.
- Los documentos PDF de ejemplo incluyen una advertencia y no son válidos para trámites.

Si un indicador aparece en cero después de cambiar periodo o moneda, prueba `Mes actual` y `COP`. Una consulta personalizada solo incluye operaciones con fechas dentro del rango seleccionado.

## 6. Detener, volver a iniciar y preparar una base limpia

- Para detener el servidor, vuelve a la terminal del script y pulsa **Ctrl+C**.
- Para iniciar la presentación otro día, ejecuta de nuevo el comando de inicio. La base local y los registros existentes se conservan; el seeder no debería duplicar las entradas demo.
- Si el puerto 8001 ya está ocupado, detén el servidor anterior o inicia en otro puerto:

  ```powershell
  powershell -ExecutionPolicy Bypass -File .\scripts\run-demo.ps1 -Port 8002
  ```

  En ese caso abre `http://127.0.0.1:8002`.

- Para empezar desde una base demo vacía, detén primero el servidor y respalda la base si quieres conservarla. Luego elimina **solo** `database\presentation.sqlite` y vuelve a ejecutar el script:

  ```powershell
  Copy-Item .\database\presentation.sqlite .\database\presentation-backup.sqlite
  Remove-Item .\database\presentation.sqlite
  powershell -ExecutionPolicy Bypass -File .\scripts\run-demo.ps1
  ```

  No ejecutes `migrate:fresh` contra la base configurada en `.env`.

## 7. Solución de problemas

- **El navegador muestra una página vacía o datos antiguos:** confirma que la dirección use el puerto actual que anunció el script (8001 o el puerto alternativo), inicia sesión de nuevo y recarga la página.
- **El puerto 8001 no abre:** revisa que la ventana de PowerShell siga mostrando `Server running`. Si otro proceso lo ocupa, prueba `-Port 8002`.
- **La contraseña no funciona:** usa la contraseña impresa por la ejecución actual del script; cambia al volverlo a iniciar.
- **El dashboard muestra cifras distintas:** selecciona `Mes actual` y `COP`. Cada tarjeta se filtra por moneda y periodo.
- **No aparecen los registros de demostración tras borrar la base:** vuelve a ejecutar `run-demo.ps1`; el script crea las tablas y carga el seeder.
