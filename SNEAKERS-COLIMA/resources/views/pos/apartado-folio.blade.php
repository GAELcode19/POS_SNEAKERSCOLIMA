<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Folio {{ $layaway->folio }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; background: #f3f4f6; color: #111; }
        .toolbar {
            position: sticky; top: 0; display: flex; gap: 12px; align-items: center;
            padding: 14px 20px; background: #111827; color: #fff;
        }
        .toolbar h1 { font-size: 15px; margin: 0; flex: 1; }
        .btn { padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-print { background: #f59e0b; color: #111; }
        .btn-ghost { background: #374151; color: #fff; }

        .ticket {
            width: 80mm; margin: 16px auto; background: #fff; padding: 6mm 6mm 8mm;
            box-shadow: 0 2px 10px rgba(0,0,0,.1);
        }
        .ticket .store { text-align: center; }
        .ticket .store h2 { margin: 0; font-size: 16px; letter-spacing: 1px; }
        .ticket .store .sub { font-size: 10px; color: #555; text-transform: uppercase; letter-spacing: 1px; }
        .ticket .doc { text-align: center; margin: 8px 0; padding: 6px 0; border-top: 1px dashed #999; border-bottom: 1px dashed #999; }
        .ticket .doc .label { font-size: 10px; color: #666; text-transform: uppercase; letter-spacing: 1px; }
        .ticket .doc .folio { font-size: 20px; font-weight: 800; font-family: 'Courier New', monospace; letter-spacing: 2px; }
        .row { display: flex; justify-content: space-between; font-size: 12px; margin: 3px 0; }
        .row .k { color: #555; }
        .row .v { font-weight: 600; text-align: right; }
        .totals { margin-top: 8px; padding-top: 8px; border-top: 1px dashed #999; }
        .totals .big { font-size: 15px; }
        .totals .pay { color: #059669; }
        .totals .rem { color: #b45309; }
        .note { margin-top: 10px; font-size: 10px; color: #444; line-height: 1.5; border-top: 1px dashed #999; padding-top: 8px; }
        .note strong { color: #111; }
        .foot { text-align: center; font-size: 10px; color: #777; margin-top: 10px; }

        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .ticket { box-shadow: none; margin: 0; width: auto; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <h1>Folio de apartado {{ $layaway->folio }}</h1>
        <button class="btn btn-print" onclick="window.print()">🖨️ Imprimir</button>
        <a href="{{ route('pos.apartados') }}" class="btn btn-ghost">Volver</a>
    </div>

    <div class="ticket">
        <div class="store">
            <h2>SNEAKERS COLIMA</h2>
            <div class="sub">Comprobante de apartado</div>
        </div>

        <div class="doc">
            <div class="label">Folio</div>
            <div class="folio">{{ $layaway->folio }}</div>
            <div class="label" style="margin-top:4px;">{{ $layaway->created_at->format('d/m/Y H:i') }}</div>
        </div>

        <div class="row"><span class="k">Producto</span><span class="v">{{ $layaway->product_name }}</span></div>
        @if($layaway->size)
            <div class="row"><span class="k">Talla</span><span class="v">{{ $layaway->size }}</span></div>
        @endif
        @if($layaway->customer_name)
            <div class="row"><span class="k">Cliente</span><span class="v">{{ $layaway->customer_name }}</span></div>
        @endif
        @if($layaway->customer_phone)
            <div class="row"><span class="k">Teléfono</span><span class="v">{{ $layaway->customer_phone }}</span></div>
        @endif
        <div class="row"><span class="k">Atendió</span><span class="v">{{ $layaway->employee?->name ?? '—' }}</span></div>

        <div class="totals">
            <div class="row big"><span class="k">Precio total</span><span class="v">${{ number_format($layaway->total_price, 2, '.', ',') }}</span></div>
            <div class="row"><span class="k pay">Anticipo / abonado</span><span class="v pay">${{ number_format($layaway->paid_amount, 2, '.', ',') }}</span></div>
            <div class="row big"><span class="k rem">Resta por liquidar</span><span class="v rem">${{ number_format($layaway->remaining(), 2, '.', ',') }}</span></div>
        </div>

        @if($layaway->payments->count() > 1)
            <div class="note">
                <strong>Historial de pagos:</strong><br>
                @foreach($layaway->payments as $pay)
                    {{ $pay->created_at->format('d/m/Y') }} — {{ ucfirst($pay->type) }}: ${{ number_format($pay->amount, 2, '.', ',') }}<br>
                @endforeach
            </div>
        @endif

        <div class="note">
            <strong>Condiciones:</strong><br>
            • Fecha límite para liquidar: <strong>{{ $layaway->due_at->format('d/m/Y') }}</strong> (30 días).<br>
            • Si no se liquida a tiempo, el producto regresa a venta y el dinero abonado queda como <strong>saldo a favor</strong>.<br>
            • El saldo a favor se respeta hasta el <strong>{{ $layaway->credit_expires_at->format('d/m/Y') }}</strong> presentando este folio.<br>
            • Conserva este comprobante: es tu única forma de reclamar el saldo.
        </div>

        <div class="foot">Gracias por tu compra · Sneakers Colima</div>
    </div>
</body>
</html>
