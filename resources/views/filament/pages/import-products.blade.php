<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Template download --}}
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px;display:flex;align-items:center;justify-content:space-between;">
            <div>
                <p style="font-size:14px;font-weight:600;color:#15803d;">Download Template</p>
                <p style="font-size:13px;color:#166534;margin-top:2px;">Use this CSV template to prepare your product data correctly.</p>
            </div>
            <a href="/templates/products-import-template.csv" download
               style="padding:8px 16px;background:#16a34a;color:white;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
                Download Template
            </a>
        </div>

        {{-- STAGE: Upload --}}
        @if($stage === 'upload')
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:4px;">Upload Product File</h2>
            <p style="font-size:13px;color:#6b7280;margin-bottom:20px;">Accepted: <strong>CSV</strong> or <strong>Excel (.xlsx)</strong>. Max 5MB. Existing products matched by SKU will be updated.</p>

            <div x-data="{ uploading: false, selectedName: '' }">
                <input type="file" accept=".csv,.xlsx"
                    style="display:block;width:100%;padding:10px;border:2px dashed #d1d5db;border-radius:8px;font-size:14px;cursor:pointer;background:#fafafa;"
                    x-on:change="
                        const file = $event.target.files[0];
                        if (!file) return;
                        selectedName = file.name;
                        uploading = true;
                        const reader = new FileReader();
                        reader.onload = async (e) => {
                            const base64 = e.target.result.split(',')[1];
                            const ext = file.name.split('.').pop().toLowerCase();
                            const resp = await fetch('/admin/import-products/upload', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                },
                                body: JSON.stringify({ file: base64, name: file.name, ext })
                            });
                            const data = await resp.json();
                            uploading = false;
                            if (data.token) {
                                await $wire.set('tempPath', data.token);
                                await $wire.call('goToPreview');
                            }
                        };
                        reader.readAsDataURL(file);
                    ">
                <p x-show="uploading" x-text="'Uploading ' + selectedName + '...'" style="font-size:13px;color:#374151;margin-top:8px;"></p>
            </div>
        </div>
        @endif

        {{-- STAGE: Preview --}}
        @if($stage === 'preview')
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:4px;">Preview (first {{ count($previewRows) }} rows)</h2>
            <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">Review the data below, then click Import to process the file.</p>

            @if(count($previewRows) > 0)
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;font-size:13px;">
                    <thead>
                        <tr style="background:#f9fafb;">
                            @foreach($previewHeaders as $header)
                            <th style="padding:8px 12px;text-align:left;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;font-size:11px;white-space:nowrap;">{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previewRows as $row)
                        <tr>
                            @foreach($row as $cell)
                            <td style="padding:8px 12px;border-bottom:1px solid #f3f4f6;color:#374151;white-space:nowrap;">{{ $cell }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p style="color:#ef4444;font-size:13px;">No data rows found in the file.</p>
            @endif

            <div style="margin-top:20px;display:flex;gap:12px;">
                <button wire:click="runImport" style="padding:10px 20px;background:#E53935;color:white;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                    Confirm Import
                </button>
                <button wire:click="resetImport" style="padding:10px 20px;background:#f3f4f6;color:#374151;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                    Cancel
                </button>
            </div>
        </div>
        @endif

        {{-- STAGE: Done --}}
        @if($stage === 'done')
        <div style="background:white;border:1px solid #e5e7eb;border-radius:12px;padding:24px;">
            <h2 style="font-size:15px;font-weight:700;color:#111827;margin-bottom:16px;">Import Complete</h2>

            <div style="display:flex;gap:16px;margin-bottom:20px;">
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px 24px;text-align:center;">
                    <p style="font-size:28px;font-weight:800;color:#15803d;">{{ $created }}</p>
                    <p style="font-size:13px;color:#166534;margin-top:4px;">Products Created</p>
                </div>
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:16px 24px;text-align:center;">
                    <p style="font-size:28px;font-weight:800;color:#1d4ed8;">{{ $updated }}</p>
                    <p style="font-size:13px;color:#1e40af;margin-top:4px;">Products Updated</p>
                </div>
                @if(count($importErrors) > 0)
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:16px 24px;text-align:center;">
                    <p style="font-size:28px;font-weight:800;color:#b91c1c;">{{ count($importErrors) }}</p>
                    <p style="font-size:13px;color:#991b1b;margin-top:4px;">Rows Skipped</p>
                </div>
                @endif
            </div>

            @if(count($importErrors) > 0)
            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:16px;margin-bottom:20px;">
                <p style="font-size:13px;font-weight:600;color:#b91c1c;margin-bottom:8px;">Skipped rows:</p>
                <ul style="list-style:disc;padding-left:20px;font-size:13px;color:#991b1b;">
                    @foreach($importErrors as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div style="display:flex;gap:12px;">
                <button wire:click="resetImport" style="padding:10px 20px;background:#E53935;color:white;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">
                    Import Another File
                </button>
                <a href="/admin/products" style="padding:10px 20px;background:#f3f4f6;color:#374151;border-radius:8px;font-size:14px;font-weight:600;text-decoration:none;">
                    View Products
                </a>
            </div>
        </div>
        @endif

    </div>
</x-filament-panels::page>
