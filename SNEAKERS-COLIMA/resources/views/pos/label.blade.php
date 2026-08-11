<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Etiquetas' }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            background: #f3f4f6;
            color: #111;
        }

        /* Barra de herramientas (no se imprime) */
        .toolbar {
            position: sticky;
            top: 0;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            background: #111827;
            color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,.15);
        }
        .toolbar h1 { font-size: 15px; margin: 0; font-weight: 700; flex: 1; }
        .toolbar label { font-size: 13px; color: #d1d5db; }
        .toolbar input {
            width: 70px; padding: 6px 8px; border-radius: 6px;
            border: 1px solid #374151; background: #1f2937; color: #fff; font-size: 13px;
        }
        .toolbar .btn {
            padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer;
            font-size: 13px; font-weight: 600; text-decoration: none; display: inline-block;
        }
        .btn-print { background: #f59e0b; color: #111; }
        .btn-ghost { background: #374151; color: #fff; }
        .hint { font-size: 12px; color: #9ca3af; width: 100%; margin-top: 2px; }

        /* Hoja de etiquetas */
        .sheet {
            display: flex;
            flex-wrap: wrap;
            gap: 6mm;
            padding: 10mm;
            justify-content: flex-start;
        }
        .label {
            width: 50mm;
            height: 30mm;
            border: 1px dashed #cbd5e1;
            border-radius: 4px;
            padding: 2mm 2.5mm;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }
        .label .store { font-size: 7px; letter-spacing: .5px; text-transform: uppercase; color: #6b7280; font-weight: 700; }
        .label .name { font-size: 10px; font-weight: 700; line-height: 1.1; margin: 1px 0; max-height: 22px; overflow: hidden; }
        .label .meta { font-size: 7.5px; color: #4b5563; }
        .label .row { display: flex; justify-content: space-between; align-items: flex-end; }
        .label .price { font-size: 13px; font-weight: 800; }
        .label .barcode { text-align: center; margin-top: 1px; }
        .label .barcode svg { width: 100%; height: 34px; }
        .label .sku { text-align: center; font-size: 8px; letter-spacing: 1px; font-family: 'Courier New', monospace; margin-top: -1px; }

        .empty { padding: 40px; text-align: center; color: #6b7280; }

        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { padding: 0; gap: 0; }
            .label { border: none; page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <h1>{{ $title ?? 'Etiquetas' }}</h1>
        @if(count($labels) === 1)
            <form method="GET" style="display:flex;align-items:center;gap:8px;">
                <label for="copies">Copias:</label>
                <input type="number" id="copies" name="copies" min="1" max="100" value="{{ $copies }}">
                <button type="submit" class="btn btn-ghost">Aplicar</button>
            </form>
        @endif
        <button type="button" class="btn btn-print" onclick="window.print()">🖨️ Imprimir</button>
        <a href="{{ route('pos.inventory') }}" class="btn btn-ghost">Volver</a>
        <p class="hint">Ajusta la impresora a etiquetas de 50 × 30 mm (o papel normal). Cada recuadro es una etiqueta lista para pegar en la prenda.</p>
    </div>

    <div class="sheet">
        @forelse($labels as $entry)
            @php $p = $entry['product']; @endphp
            @for($i = 0; $i < $copies; $i++)
                <div class="label">
                    <div>
                        <div class="store">Sneakers Colima</div>
                        <div class="name">{{ $p->name }}</div>
                        <div class="meta">
                            {{ $p->brand }}@if($p->colorway) · {{ $p->colorway }}@endif
                        </div>
                    </div>
                    <div class="row">
                        <span class="meta">{{ $p->category }}</span>
                        <span class="price">${{ number_format($p->price, 0, '.', ',') }}</span>
                    </div>
                    <div>
                        <div class="barcode">{!! $entry['barcode'] !!}</div>
                        <div class="sku">{{ $p->sku }}</div>
                    </div>
                </div>
            @endfor
        @empty
            <div class="empty">No hay productos para generar etiquetas.</div>
        @endforelse
    </div>

    <script>
        // Al abrir para imprimir todas, lanzar el diálogo automáticamente es molesto;
        // el usuario decide con el botón Imprimir.
    </script>
</body>
</html>
