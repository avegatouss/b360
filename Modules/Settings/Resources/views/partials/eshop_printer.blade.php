@php
    use Illuminate\Support\Facades\Cache;
    $instanceId = $instance->id ?? 0;
    $printerSettings = Cache::get("eshop_printer_settings_{$instanceId}", []);
@endphp

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Imprimante de recus</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Type de connexion</label>
                <select name="settings[printer_type]" class="form-select">
                    @php $type = $values['printer_type'] ?? $printerSettings['printer_type'] ?? 'network'; @endphp
                    <option value="network" @selected($type === 'network')>Reseau (TCP/IP)</option>
                    <option value="windows" @selected($type === 'windows')>Windows (partage reseau)</option>
                    <option value="cups" @selected($type === 'cups')>CUPS (Linux/Mac)</option>
                    <option value="usb" @selected($type === 'usb')>USB direct</option>
                </select>
                <input type="hidden" name="types[printer_type]" value="string">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Nom de l'imprimante</label>
                <input type="text" name="settings[receipt_printer]" class="form-control"
                       value="{{ $values['receipt_printer'] ?? $printerSettings['receipt_printer'] ?? '' }}"
                       placeholder="Ex: Epson TM-T20III">
                <input type="hidden" name="types[receipt_printer]" value="string">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Adresse IP / Hote</label>
                <input type="text" name="settings[printer_host]" class="form-control"
                       value="{{ $values['printer_host'] ?? $printerSettings['printer_host'] ?? '' }}"
                       placeholder="192.168.1.100">
                <input type="hidden" name="types[printer_host]" value="string">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Port</label>
                <input type="number" name="settings[printer_port]" class="form-control"
                       value="{{ $values['printer_port'] ?? $printerSettings['printer_port'] ?? 9100 }}"
                       min="1" max="65535">
                <input type="hidden" name="types[printer_port]" value="integer">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Partage reseau (Windows)</label>
                <input type="text" name="settings[printer_share]" class="form-control"
                       value="{{ $values['printer_share'] ?? $printerSettings['printer_share'] ?? '' }}"
                       placeholder="\\\\SERVER\\PRINTER">
                <input type="hidden" name="types[printer_share]" value="string">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Largeur du recu (mm)</label>
                <select name="settings[receipt_width]" class="form-select">
                    @php $width = (int)($values['receipt_width'] ?? $printerSettings['receipt_width'] ?? 80); @endphp
                    <option value="58" @selected($width === 58)>58 mm</option>
                    <option value="80" @selected($width === 80)>80 mm</option>
                </select>
                <input type="hidden" name="types[receipt_width]" value="integer">
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Contenu du recu</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">En-tete du recu</label>
            <textarea name="settings[receipt_header]" class="form-control" rows="2"
                      placeholder="Nom de l'entreprise, adresse...">{{ $values['receipt_header'] ?? $printerSettings['receipt_header'] ?? '' }}</textarea>
            <input type="hidden" name="types[receipt_header]" value="string">
        </div>
        <div class="mb-3">
            <label class="form-label">Pied de page du recu</label>
            <textarea name="settings[receipt_footer]" class="form-control" rows="2"
                      placeholder="Merci pour votre achat !">{{ $values['receipt_footer'] ?? $printerSettings['receipt_footer'] ?? '' }}</textarea>
            <input type="hidden" name="types[receipt_footer]" value="string">
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="settings[print_logo]" value="0">
                    <input class="form-check-input" type="checkbox"
                           name="settings[print_logo]" value="1" id="printerLogo"
                           @checked(($values['print_logo'] ?? $printerSettings['print_logo'] ?? false) == true)>
                    <input type="hidden" name="types[print_logo]" value="boolean">
                    <label class="form-check-label" for="printerLogo">Imprimer le logo</label>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="settings[auto_print_receipt]" value="0">
                    <input class="form-check-input" type="checkbox"
                           name="settings[auto_print_receipt]" value="1" id="printerAuto"
                           @checked(($values['auto_print_receipt'] ?? $printerSettings['auto_print_receipt'] ?? false) == true)>
                    <input type="hidden" name="types[auto_print_receipt]" value="boolean">
                    <label class="form-check-label" for="printerAuto">Impression automatique</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Imprimante cuisine</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="form-check form-switch">
                    <input type="hidden" name="settings[print_kitchen_order]" value="0">
                    <input class="form-check-input" type="checkbox"
                           name="settings[print_kitchen_order]" value="1" id="printerKitchen"
                           @checked(($values['print_kitchen_order'] ?? $printerSettings['print_kitchen_order'] ?? false) == true)>
                    <input type="hidden" name="types[print_kitchen_order]" value="boolean">
                    <label class="form-check-label" for="printerKitchen">Imprimer les commandes cuisine</label>
                </div>
            </div>
            <div class="col-md-8 mb-3">
                <label class="form-label">Nom de l'imprimante cuisine</label>
                <input type="text" name="settings[kitchen_printer]" class="form-control"
                       value="{{ $values['kitchen_printer'] ?? $printerSettings['kitchen_printer'] ?? '' }}">
                <input type="hidden" name="types[kitchen_printer]" value="string">
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Impression de codes-barres</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Imprimante codes-barres</label>
                <input type="text" name="settings[barcode_printer]" class="form-control"
                       value="{{ $values['barcode_printer'] ?? $printerSettings['barcode_printer'] ?? '' }}">
                <input type="hidden" name="types[barcode_printer]" value="string">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Largeur etiquette (mm)</label>
                <input type="number" name="settings[barcode_label_width]" class="form-control"
                       value="{{ $values['barcode_label_width'] ?? $printerSettings['barcode_label_width'] ?? 40 }}"
                       min="20" max="120">
                <input type="hidden" name="types[barcode_label_width]" value="integer">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Hauteur etiquette (mm)</label>
                <input type="number" name="settings[barcode_label_height]" class="form-control"
                       value="{{ $values['barcode_label_height'] ?? $printerSettings['barcode_label_height'] ?? 30 }}"
                       min="10" max="80">
                <input type="hidden" name="types[barcode_label_height]" value="integer">
            </div>
        </div>
    </div>
</div>
