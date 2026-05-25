@extends('layouts.pos')

@section('title', 'Inventario - Sneakers Colima')

@section('content')
<div>
    <div class="inventory-header">
        <div class="page-header-left">
            <h1>Inventario</h1>
            <div class="subtitle">Control de stock &middot; {{ $totalProducts }} productos registrados</div>
        </div>
        <div class="page-header-right">
            <a href="{{ route('pos.inventory.export') }}" class="btn btn-secondary" style="text-decoration:none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Exportar
            </a>
            <button class="btn btn-primary" onclick="openCreateModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Nuevo Producto
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="inventory-stats">
        <div class="kpi-card">
            <div class="kpi-card-icon sales">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
            </div>
            <h3>{{ $totalProducts }}</h3>
            <div class="kpi-label">Productos</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-card-icon orders">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline></svg>
            </div>
            <h3>{{ $totalStock }}</h3>
            <div class="kpi-label">Unidades en Stock</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-card-icon ticket">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            </div>
            <h3>{{ $lowStock }}</h3>
            <div class="kpi-label">Stock Bajo</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-card-icon clients">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
            <h3>${{ number_format($totalValue, 0, '.', ',') }}</h3>
            <div class="kpi-label">Valor Total</div>
        </div>
    </div>

    {{-- Filters --}}
    <div style="display:flex;gap:16px;margin-bottom:20px;align-items:center;">
        <form method="GET" action="{{ route('pos.inventory') }}" class="search-bar" style="flex:1;margin-bottom:0;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar por nombre o SKU...">
            @if($brandFilter !== 'todos')
                <input type="hidden" name="brand" value="{{ $brandFilter }}">
            @endif
        </form>
        <div class="filter-tabs" style="margin-bottom:0;">
            @foreach($brands as $brand)
                <a
                    href="{{ route('pos.inventory', ['brand' => $brand, 'search' => $search ?: null]) }}"
                    class="filter-tab {{ $brandFilter === $brand ? 'active' : '' }}"
                >
                    {{ $brand === 'todos' ? 'Todos' : $brand }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Table --}}
    <div class="inventory-table-wrap">
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>SKU</th>
                    <th>Marca</th>
                    <th>Categor&iacute;a</th>
                    <th>Costo</th>
                    <th>Precio</th>
                    <th>Tallas</th>
                    <th>Stock</th>
                    <th>Valor</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>
                            <div class="inventory-product-cell">
                                <div class="inventory-product-thumb product-thumb-clickable" onclick="openImageModal({{ $product->id }}, '{{ $product->image ? asset('storage/' . $product->image) : '' }}')" title="Cambiar imagen">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        <div style="width:100%;height:100%;background:#2a2a2a;display:flex;align-items:center;justify-content:center;font-size:16px;">👟</div>
                                    @endif
                                    <div class="thumb-overlay">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                                    </div>
                                </div>
                                <div>
                                    <strong>{{ $product->name }}</strong>
                                    <div style="font-size:11px;color:var(--text-muted);">{{ $product->colorway }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="color:var(--text-muted);font-family:monospace;font-size:12px;">{{ $product->sku }}</td>
                        <td>{{ $product->brand }}</td>
                        <td>{{ $product->category }}</td>
                        <td style="color:var(--text-muted);font-size:13px;">${{ number_format($product->cost, 0, '.', ',') }}</td>
                        <td style="font-weight:600;">${{ number_format($product->price, 0, '.', ',') }}</td>
                        <td>
                            @if($product->sizes->count() > 0)
                                <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                    @foreach($product->sizes->sortBy('size') as $ps)
                                        <span style="display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600;background:{{ $ps->stock > 0 ? 'var(--green-dim)' : 'rgba(239,68,68,0.15)' }};color:{{ $ps->stock > 0 ? 'var(--green)' : 'var(--red)' }};">{{ $ps->size }}<span style="opacity:0.7;margin-left:2px;">({{ $ps->stock }})</span></span>
                                    @endforeach
                                </div>
                            @else
                                <span style="font-size:11px;color:var(--text-muted);">Sin tallas</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-weight:700;">{{ $product->stock }}</span>
                        </td>
                        <td style="color:var(--text-muted);">${{ number_format($product->price * $product->stock, 0, '.', ',') }}</td>
                        <td>
                            @if($product->stock <= 0)
                                <span class="stock-indicator low">Agotado</span>
                            @elseif($product->stock <= 3)
                                <span class="stock-indicator medium">Bajo</span>
                            @else
                                <span class="stock-indicator high">OK</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <button class="btn-edit-product" onclick="openEditModal({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ addslashes($product->colorway) }}', '{{ $product->sku }}', '{{ $product->brand }}', '{{ $product->category }}', {{ $product->price }}, {{ $product->cost }}, {{ $product->stock }}, '{{ $product->image ? asset('storage/' . $product->image) : '' }}')" title="Editar producto">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    Editar
                                </button>
                                <button class="btn-edit-product" style="background:var(--blue-dim);color:var(--blue);" data-product-id="{{ $product->id }}" data-product-name="{{ $product->name }}" data-product-sizes="{{ $product->sizes->toJson() }}" data-product-category="{{ $product->category }}" onclick="openSizesModal(this)" title="Gestionar tallas">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                                    Tallas
                                </button>
                                <form method="POST" action="{{ route('pos.inventory.destroy', $product) }}" style="display:inline;" onsubmit="return confirm('¿Eliminar {{ addslashes($product->name) }}? Esta acción no se puede deshacer.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-edit-product" style="background:rgba(239,68,68,0.15);color:var(--red);" title="Eliminar producto">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Cambiar Imagen (click en thumbnail) --}}
<div id="imageModal" class="modal-overlay" style="display:none;">
    <div class="modal-content" style="max-width:400px;">
        <div class="modal-header">
            <h2>Cambiar Imagen</h2>
            <button class="modal-close" onclick="closeImageModal()">&times;</button>
        </div>
        <form id="imageForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            <div class="modal-body" style="align-items:center;">
                <div class="image-upload-area" id="imageUploadArea" onclick="document.getElementById('image_file').click()">
                    <div id="imagePreviewContainer" class="image-preview-container">
                        <svg id="imageUploadIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:48px;height:48px;color:var(--text-muted);"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                        <img id="imagePreview" src="" style="display:none;width:100%;height:100%;object-fit:cover;border-radius:12px;">
                    </div>
                    <p style="margin:12px 0 0;font-size:13px;color:var(--text-muted);">Click para seleccionar imagen</p>
                </div>
                <input type="file" name="image" id="image_file" accept="image/*" style="display:none;" onchange="previewImage(this, 'imagePreview', 'imageUploadIcon')">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeImageModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Imagen</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Editar Producto --}}
<div id="editProductModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Editar Producto</h2>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editProductForm" method="POST" action="" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group" style="align-items:center;">
                    <label>Imagen del Producto</label>
                    <div class="image-upload-area image-upload-small" onclick="document.getElementById('edit_image_file').click()">
                        <div class="image-preview-container" style="width:80px;height:80px;">
                            <svg id="editImageUploadIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:32px;height:32px;color:var(--text-muted);"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                            <img id="editImagePreview" src="" style="display:none;width:100%;height:100%;object-fit:cover;border-radius:12px;">
                        </div>
                        <span style="font-size:12px;color:var(--text-muted);">Cambiar imagen</span>
                    </div>
                    <input type="file" name="image" id="edit_image_file" accept="image/*" style="display:none;" onchange="previewImage(this, 'editImagePreview', 'editImageUploadIcon')">
                </div>
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="name" id="edit_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label>Colorway</label>
                    <input type="text" name="colorway" id="edit_colorway" class="form-input">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>SKU</label>
                        <div class="brand-select-wrap">
                            <input type="text" name="sku" id="edit_sku" class="form-input" required>
                            <button type="button" class="btn-add-brand" onclick="generateSku('edit')" title="Generar SKU autom&aacute;tico">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Marca</label>
                        <div class="brand-select-wrap">
                            <select name="brand" id="edit_brand" class="form-input brand-select">
                                @foreach($brands as $brand)
                                    @if($brand !== 'todos')
                                        <option value="{{ $brand }}">{{ $brand }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button type="button" class="btn-add-brand" onclick="openAddBrand('edit_brand')" title="Agregar marca">+</button>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Categor&iacute;a</label>
                        <div class="brand-select-wrap">
                            <select name="category" id="edit_category" class="form-input category-select">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn-add-brand" onclick="openAddCategory('edit_category')" title="Agregar categor&iacute;a">+</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Precio</label>
                        <input type="number" name="price" id="edit_price" class="form-input" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Costo (lo que te cost&oacute;)</label>
                        <input type="number" name="cost" id="edit_cost" class="form-input" min="0" step="0.01" value="0">
                    </div>
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" name="stock" id="edit_stock" class="form-input" min="0" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Nuevo Producto --}}
<div id="createProductModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Nuevo Producto</h2>
            <button class="modal-close" onclick="closeCreateModal()">&times;</button>
        </div>
        <form method="POST" action="{{ route('pos.inventory.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-group" style="align-items:center;">
                    <label>Imagen del Producto</label>
                    <div class="image-upload-area" onclick="document.getElementById('create_image_file').click()">
                        <div class="image-preview-container">
                            <svg id="createImageUploadIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="width:48px;height:48px;color:var(--text-muted);"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                            <img id="createImagePreview" src="" style="display:none;width:100%;height:100%;object-fit:cover;border-radius:12px;">
                        </div>
                        <p style="margin:12px 0 0;font-size:13px;color:var(--text-muted);">Click para seleccionar imagen</p>
                    </div>
                    <input type="file" name="image" id="create_image_file" accept="image/*" style="display:none;" onchange="previewImage(this, 'createImagePreview', 'createImageUploadIcon')">
                </div>
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" name="name" id="create_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label>Colorway</label>
                    <input type="text" name="colorway" id="create_colorway" class="form-input">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>SKU</label>
                        <div class="brand-select-wrap">
                            <input type="text" name="sku" id="create_sku" class="form-input" required>
                            <button type="button" class="btn-add-brand" onclick="generateSku('create')" title="Generar SKU autom&aacute;tico">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Marca</label>
                        <div class="brand-select-wrap">
                            <select name="brand" id="create_brand" class="form-input brand-select">
                                @foreach($brands as $brand)
                                    @if($brand !== 'todos')
                                        <option value="{{ $brand }}">{{ $brand }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button type="button" class="btn-add-brand" onclick="openAddBrand('create_brand')" title="Agregar marca">+</button>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Categor&iacute;a</label>
                        <div class="brand-select-wrap">
                            <select name="category" id="create_category" class="form-input category-select">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn-add-brand" onclick="openAddCategory('create_category')" title="Agregar categor&iacute;a">+</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Precio</label>
                        <input type="number" name="price" class="form-input" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Costo (lo que te cost&oacute;)</label>
                        <input type="number" name="cost" class="form-input" min="0" step="0.01" value="0">
                    </div>
                    <div class="form-group">
                        <label>Stock</label>
                        <input type="number" name="stock" class="form-input" min="0" required value="0">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Crear Producto</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Gestionar Marcas --}}
<div id="addBrandModal" class="modal-overlay" style="display:none;z-index:1100;">
    <div class="modal-content" style="max-width:400px;">
        <div class="modal-header">
            <h2>Gestionar Marcas</h2>
            <button class="modal-close" onclick="closeAddBrand()">&times;</button>
        </div>
        <div class="modal-body" style="padding:16px;">
            <div id="brandList" style="max-height:250px;overflow-y:auto;margin-bottom:12px;">
                @foreach($brands as $brand)
                    @if($brand !== 'todos')
                    <div class="manage-item" id="brand-item-{{ Str::slug($brand) }}" style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius);margin-bottom:6px;">
                        <span style="font-weight:600;font-size:14px;">{{ $brand }}</span>
                        <button type="button" onclick="deleteBrand('{{ addslashes($brand) }}', '{{ Str::slug($brand) }}')" style="background:none;border:none;color:var(--red);cursor:pointer;padding:4px;font-size:16px;opacity:0.7;transition:opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'" title="Eliminar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                    @endif
                @endforeach
            </div>
            <div style="border-top:1px solid var(--border-color);padding-top:12px;">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block;">Agregar nueva marca</label>
                <div style="display:flex;gap:8px;">
                    <input type="text" id="new_brand_name" class="form-input" placeholder="Ej: Puma, Reebok..." style="flex:1;">
                    <button type="button" class="btn btn-primary" onclick="saveBrand()" style="white-space:nowrap;">Agregar</button>
                </div>
                <div id="brandError" style="display:none;color:var(--red);font-size:12px;margin-top:6px;"></div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Gestionar Categorías --}}
<div id="addCategoryModal" class="modal-overlay" style="display:none;z-index:1100;">
    <div class="modal-content" style="max-width:400px;">
        <div class="modal-header">
            <h2>Gestionar Categor&iacute;as</h2>
            <button class="modal-close" onclick="closeAddCategory()">&times;</button>
        </div>
        <div class="modal-body" style="padding:16px;">
            <div id="categoryList" style="max-height:250px;overflow-y:auto;margin-bottom:12px;">
                @foreach($categories as $cat)
                    <div class="manage-item" id="cat-item-{{ Str::slug($cat) }}" style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius);margin-bottom:6px;">
                        <span style="font-weight:600;font-size:14px;">{{ $cat }}</span>
                        <button type="button" onclick="deleteCategory('{{ addslashes($cat) }}', '{{ Str::slug($cat) }}')" style="background:none;border:none;color:var(--red);cursor:pointer;padding:4px;font-size:16px;opacity:0.7;transition:opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'" title="Eliminar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                @endforeach
            </div>
            <div style="border-top:1px solid var(--border-color);padding-top:12px;">
                <label style="font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;display:block;">Agregar nueva categor&iacute;a</label>
                <div style="display:flex;gap:8px;">
                    <input type="text" id="new_category_name" class="form-input" placeholder="Ej: Perfumes, Gorras..." style="flex:1;">
                    <button type="button" class="btn btn-primary" onclick="saveCategory()" style="white-space:nowrap;">Agregar</button>
                </div>
                <div id="categoryError" style="display:none;color:var(--red);font-size:12px;margin-top:6px;"></div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Gestionar Tallas --}}
<div id="sizesModal" class="modal-overlay" style="display:none;" onclick="if(event.target===this)document.getElementById('sizesModal').style.display='none'">
    <div class="modal-content" style="max-width:500px;">
        <div class="modal-header">
            <h2>Gestionar Tallas</h2>
            <button class="modal-close" onclick="document.getElementById('sizesModal').style.display='none'">&times;</button>
        </div>
        <form id="sizesForm" method="POST" action="">
            @csrf
            <div class="modal-body">
                <h3 id="sizesProductName" style="margin:0 0 4px;font-size:16px;"></h3>
                <p style="font-size:12px;color:var(--text-muted);margin:0 0 16px;">Agrega las tallas disponibles y su stock</p>
                <div id="sizesContainer"></div>
                <button type="button" class="btn btn-secondary" style="width:100%;justify-content:center;margin-top:8px;" onclick="addSizeRow()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Agregar talla
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('sizesModal').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Tallas</button>
            </div>
        </form>
    </div>
</div>

<script>
// === Preview image helper ===
function previewImage(input, previewId, iconId) {
    var preview = document.getElementById(previewId);
    var icon = document.getElementById(iconId);
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            icon.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// === Image modal (click on thumbnail) ===
function openImageModal(id, currentImage) {
    document.getElementById('imageForm').action = '/pos/inventory/' + id + '/image';
    var preview = document.getElementById('imagePreview');
    var icon = document.getElementById('imageUploadIcon');
    if (currentImage) {
        preview.src = currentImage;
        preview.style.display = 'block';
        icon.style.display = 'none';
    } else {
        preview.style.display = 'none';
        preview.src = '';
        icon.style.display = 'block';
    }
    document.getElementById('image_file').value = '';
    document.getElementById('imageModal').style.display = 'flex';
}
function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) closeImageModal();
});

// === Edit modal ===
function generateSku(prefix) {
    var brand = document.getElementById(prefix + '_brand').value || '';
    var name = document.getElementById(prefix + '_name').value || '';
    var colorway = document.getElementById(prefix + '_colorway').value || '';

    function initials(str) {
        return str.trim().split(/\s+/).map(function(w) {
            return w.substring(0, 2).toUpperCase();
        }).join('');
    }

    var parts = [];
    if (brand) parts.push(initials(brand));
    if (name) parts.push(initials(name));
    if (colorway) parts.push(initials(colorway));

    if (parts.length === 0) {
        parts.push('SKU');
    }

    var random = Math.floor(Math.random() * 900 + 100);
    var sku = parts.join('-') + '-' + random;

    document.getElementById(prefix + '_sku').value = sku;
}

function openEditModal(id, name, colorway, sku, brand, category, price, cost, stock, image) {
    document.getElementById('editProductForm').action = '/pos/inventory/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_colorway').value = colorway;
    document.getElementById('edit_sku').value = sku;
    document.getElementById('edit_brand').value = brand;
    document.getElementById('edit_category').value = category;
    document.getElementById('edit_price').value = price;
    document.getElementById('edit_cost').value = cost;
    document.getElementById('edit_stock').value = stock;
    var preview = document.getElementById('editImagePreview');
    var icon = document.getElementById('editImageUploadIcon');
    if (image) {
        preview.src = image;
        preview.style.display = 'block';
        icon.style.display = 'none';
    } else {
        preview.style.display = 'none';
        preview.src = '';
        icon.style.display = 'block';
    }
    document.getElementById('edit_image_file').value = '';
    document.getElementById('editProductModal').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('editProductModal').style.display = 'none';
}
document.getElementById('editProductModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

// === Create modal ===
function openCreateModal() {
    document.getElementById('createImagePreview').style.display = 'none';
    document.getElementById('createImagePreview').src = '';
    document.getElementById('createImageUploadIcon').style.display = 'block';
    document.getElementById('create_image_file').value = '';
    document.getElementById('createProductModal').style.display = 'flex';
}
function closeCreateModal() {
    document.getElementById('createProductModal').style.display = 'none';
}
document.getElementById('createProductModal').addEventListener('click', function(e) {
    if (e.target === this) closeCreateModal();
});

// === Add Brand ===
var _targetBrandSelect = null;

function openAddBrand(selectId) {
    _targetBrandSelect = selectId;
    document.getElementById('new_brand_name').value = '';
    document.getElementById('brandError').style.display = 'none';
    document.getElementById('addBrandModal').style.display = 'flex';
    setTimeout(function() { document.getElementById('new_brand_name').focus(); }, 100);
}

function closeAddBrand() {
    document.getElementById('addBrandModal').style.display = 'none';
    _targetBrandSelect = null;
}

document.getElementById('addBrandModal').addEventListener('click', function(e) {
    if (e.target === this) closeAddBrand();
});

document.getElementById('new_brand_name').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); saveBrand(); }
});

function saveBrand() {
    var name = document.getElementById('new_brand_name').value.trim();
    var errorEl = document.getElementById('brandError');
    if (!name) {
        errorEl.textContent = 'Ingresa un nombre de marca.';
        errorEl.style.display = 'block';
        return;
    }

    fetch('{{ route("pos.brands.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name: name })
    })
    .then(function(r) {
        if (!r.ok) return r.json().then(function(d) { throw d; });
        return r.json();
    })
    .then(function(data) {
        var allSelects = document.querySelectorAll('.brand-select');
        allSelects.forEach(function(sel) {
            var opt = document.createElement('option');
            opt.value = data.name;
            opt.textContent = data.name;
            sel.appendChild(opt);
        });
        if (_targetBrandSelect) {
            document.getElementById(_targetBrandSelect).value = data.name;
        }
        var slug = data.name.toLowerCase().replace(/[^a-z0-9]+/g, '-');
        var list = document.getElementById('brandList');
        var div = document.createElement('div');
        div.className = 'manage-item';
        div.id = 'brand-item-' + slug;
        div.style.cssText = 'display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius);margin-bottom:6px;';
        div.innerHTML = '<span style="font-weight:600;font-size:14px;">' + data.name + '</span>' +
            '<button type="button" onclick="deleteBrand(\'' + data.name.replace(/'/g, "\\'") + '\', \'' + slug + '\')" style="background:none;border:none;color:var(--red);cursor:pointer;padding:4px;font-size:16px;opacity:0.7;" title="Eliminar">' +
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>';
        list.appendChild(div);
        document.getElementById('new_brand_name').value = '';
        errorEl.style.display = 'none';
    })
    .catch(function(err) {
        var msg = 'Error al crear la marca.';
        if (err && err.errors && err.errors.name) {
            msg = err.errors.name[0];
        }
        errorEl.textContent = msg;
        errorEl.style.display = 'block';
    });
}

function deleteBrand(name, slug) {
    if (!confirm('¿Eliminar la marca "' + name + '"?')) return;
    var errorEl = document.getElementById('brandError');
    fetch('/pos/brands/' + encodeURIComponent(name), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(function(r) {
        if (!r.ok) return r.json().then(function(d) { throw d; });
        return r.json();
    })
    .then(function() {
        var el = document.getElementById('brand-item-' + slug);
        if (el) el.remove();
        var allSelects = document.querySelectorAll('.brand-select');
        allSelects.forEach(function(sel) {
            var opts = sel.querySelectorAll('option');
            opts.forEach(function(opt) {
                if (opt.value === name) opt.remove();
            });
        });
        errorEl.style.display = 'none';
    })
    .catch(function(err) {
        errorEl.textContent = err.error || 'Error al eliminar la marca.';
        errorEl.style.display = 'block';
    });
}

// === Add Category ===
var _targetCategorySelect = null;

function openAddCategory(selectId) {
    _targetCategorySelect = selectId;
    document.getElementById('new_category_name').value = '';
    document.getElementById('categoryError').style.display = 'none';
    document.getElementById('addCategoryModal').style.display = 'flex';
    setTimeout(function() { document.getElementById('new_category_name').focus(); }, 100);
}

function closeAddCategory() {
    document.getElementById('addCategoryModal').style.display = 'none';
    _targetCategorySelect = null;
}

document.getElementById('addCategoryModal').addEventListener('click', function(e) {
    if (e.target === this) closeAddCategory();
});

document.getElementById('new_category_name').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); saveCategory(); }
});

function saveCategory() {
    var name = document.getElementById('new_category_name').value.trim();
    var errorEl = document.getElementById('categoryError');
    if (!name) {
        errorEl.textContent = 'Ingresa un nombre de categoría.';
        errorEl.style.display = 'block';
        return;
    }

    fetch('{{ route("pos.categories.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name: name })
    })
    .then(function(r) {
        if (!r.ok) return r.json().then(function(d) { throw d; });
        return r.json();
    })
    .then(function(data) {
        var allSelects = document.querySelectorAll('.category-select');
        allSelects.forEach(function(sel) {
            var opt = document.createElement('option');
            opt.value = data.name;
            opt.textContent = data.name;
            sel.appendChild(opt);
        });
        if (_targetCategorySelect) {
            document.getElementById(_targetCategorySelect).value = data.name;
        }
        var slug = data.name.toLowerCase().replace(/[^a-z0-9]+/g, '-');
        var list = document.getElementById('categoryList');
        var div = document.createElement('div');
        div.className = 'manage-item';
        div.id = 'cat-item-' + slug;
        div.style.cssText = 'display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:var(--radius);margin-bottom:6px;';
        div.innerHTML = '<span style="font-weight:600;font-size:14px;">' + data.name + '</span>' +
            '<button type="button" onclick="deleteCategory(\'' + data.name.replace(/'/g, "\\'") + '\', \'' + slug + '\')" style="background:none;border:none;color:var(--red);cursor:pointer;padding:4px;font-size:16px;opacity:0.7;" title="Eliminar">' +
            '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>';
        list.appendChild(div);
        document.getElementById('new_category_name').value = '';
        errorEl.style.display = 'none';
    })
    .catch(function(err) {
        var msg = 'Error al crear la categoría.';
        if (err && err.errors && err.errors.name) {
            msg = err.errors.name[0];
        }
        errorEl.textContent = msg;
        errorEl.style.display = 'block';
    });
}

function deleteCategory(name, slug) {
    if (!confirm('¿Eliminar la categoría "' + name + '"?')) return;
    var errorEl = document.getElementById('categoryError');
    fetch('/pos/categories/' + encodeURIComponent(name), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(function(r) {
        if (!r.ok) return r.json().then(function(d) { throw d; });
        return r.json();
    })
    .then(function() {
        var el = document.getElementById('cat-item-' + slug);
        if (el) el.remove();
        var allSelects = document.querySelectorAll('.category-select');
        allSelects.forEach(function(sel) {
            var opts = sel.querySelectorAll('option');
            opts.forEach(function(opt) {
                if (opt.value === name) opt.remove();
            });
        });
        errorEl.style.display = 'none';
    })
    .catch(function(err) {
        errorEl.textContent = err.error || 'Error al eliminar la categoría.';
        errorEl.style.display = 'block';
    });
}

// === Sizes modal ===
var _sizeRowIndex = 0;
var _currentCategory = '';

var _unitallaCats = ['gorra','gorras','accesorio','accesorios','perfume','perfumes','fragancia','fragancias','llavero','llaveros','calceta','calcetas','calcetines'];
var _ropaCats = ['ropa','playera','playeras','camiseta','camisetas','hoodie','hoodies','sudadera','sudaderas','pants','pantalon','pantalones','short','shorts','chamarra','chamarras'];

function getSuggestedSizes(category) {
    var cat = category.toLowerCase();
    if (_unitallaCats.indexOf(cat) !== -1) return ['Unitalla'];
    if (_ropaCats.indexOf(cat) !== -1) return ['XS','S','M','L','XL','XXL'];
    return ['22','22.5','23','23.5','24','24.5','25','25.5','26','26.5','27','27.5','28','28.5','29','29.5','30','30.5','31'];
}

function openSizesModal(el) {
    var id = el.getAttribute('data-product-id');
    var name = el.getAttribute('data-product-name');
    var sizes = JSON.parse(el.getAttribute('data-product-sizes') || '[]');
    _currentCategory = el.getAttribute('data-product-category') || '';

    document.getElementById('sizesProductName').textContent = name;
    document.getElementById('sizesForm').action = '/pos/inventory/' + id + '/sizes';
    document.getElementById('sizesContainer').innerHTML = '';
    _sizeRowIndex = 0;

    if (sizes.length > 0) {
        for (var i = 0; i < sizes.length; i++) {
            addSizeRow(sizes[i].size, sizes[i].stock);
        }
    } else {
        var suggested = getSuggestedSizes(_currentCategory);
        for (var j = 0; j < suggested.length; j++) {
            addSizeRow(suggested[j], 0);
        }
    }

    document.getElementById('sizesModal').style.display = 'flex';
}

function addSizeRow(size, stock) {
    size = size || '';
    stock = stock || 0;
    var idx = _sizeRowIndex++;
    var container = document.getElementById('sizesContainer');

    var row = document.createElement('div');
    row.style.cssText = 'display:flex;gap:8px;align-items:center;margin-bottom:8px;';

    var sizeInput = document.createElement('input');
    sizeInput.type = 'text';
    sizeInput.name = 'sizes[' + idx + '][size]';
    sizeInput.value = size;
    sizeInput.placeholder = 'Ej: 27, M, Unitalla';
    sizeInput.className = 'form-input';
    sizeInput.style.cssText = 'flex:1;';
    sizeInput.required = true;

    var stockInput = document.createElement('input');
    stockInput.type = 'number';
    stockInput.name = 'sizes[' + idx + '][stock]';
    stockInput.value = stock;
    stockInput.min = '0';
    stockInput.placeholder = 'Stock';
    stockInput.className = 'form-input';
    stockInput.style.cssText = 'width:80px;';
    stockInput.required = true;

    var removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.innerHTML = '&times;';
    removeBtn.style.cssText = 'background:none;border:none;color:var(--red);font-size:20px;cursor:pointer;padding:0 4px;';
    removeBtn.onclick = function() { row.remove(); };

    row.appendChild(sizeInput);
    row.appendChild(stockInput);
    row.appendChild(removeBtn);
    container.appendChild(row);
}
</script>
@endsection
