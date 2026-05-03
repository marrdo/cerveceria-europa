# OCR y lectura asistida de albaranes

## Que es OCR

OCR significa reconocimiento optico de caracteres. Es una tecnica que convierte texto dentro de una imagen o PDF escaneado en texto que el programa puede procesar.

Ejemplo practico:

1. El encargado hace una foto a un albaran.
2. El sistema detecta zonas con texto.
3. Extrae palabras, numeros, columnas y totales.
4. La aplicacion intenta convertirlo en datos de compra.

## Limite importante

OCR no entiende siempre el documento. Puede leer mal una referencia, confundir una cantidad o perder una linea si la foto sale torcida, con sombra o borrosa.

Para Cerveceria Europa no vamos a hacer que una foto actualice stock directamente. La opcion profesional es lectura asistida:

1. Subida de foto o PDF.
2. Lectura OCR o IA multimodal.
3. Borrador de compra.
4. Pantalla de revision humana.
5. Confirmacion por encargado, propietario o superadmin.
6. Entrada real en inventario.

## Opcion recomendada

Primero construiremos inventario y compras manuales bien hechos. Despues anadiremos un modulo `LecturasDocumentos` o `ImportacionesAlbaranes` que proponga datos, pero no confirme stock sin revision.

Esto evita errores caros y deja trazabilidad: imagen original, texto extraido, JSON interpretado, usuario revisor y fecha de confirmacion.

## Fase 3.0 implementada

La primera base ya esta implementada dentro del modulo `Compras`:

- Pantalla `Compras > Documentos`.
- Subida de JPG, PNG o PDF.
- Archivo original guardado en disco privado `local`.
- Tabla `documentos_compra` para el documento original.
- Tabla `lecturas_documentos` para intentos de OCR/IA.
- Tabla `borradores_compra_documento` para datos revisables antes de confirmar.

En esta fase no hay OCR real conectado todavia. Al subir un documento se crea:

```text
documento_compra
-> lectura pendiente
-> borrador pendiente de revision
```

La siguiente mejora natural sera conectar un motor OCR/IA que rellene `texto_extraido` y `datos_extraidos`, siempre manteniendo revision humana antes de crear pedidos, recepciones o movimientos de inventario.
