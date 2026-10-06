<script>
  function previewProduct(id) {
    fetch(`{{ url('admin/products') }}/${id}`)
      .then(res => res.json())
      .then(p => {
        let nutritionHtml = '';
        if (p.nutrition && Object.keys(p.nutrition).length) {
          nutritionHtml = Object.entries(p.nutrition).map(([k, v]) => `
            <div class="stat-card" style="padding:10px">
              <div class="stat-label">${k}</div>
              <div class="stat-value" style="font-size:16px;margin-top:4px">${v}</div>
            </div>
          `).join('');
        } else {
          nutritionHtml = '<div style="color:var(--ink-400);font-size:11px">No nutrition breakdown entered.</div>';
        }

        let flagsHtml = '';
        if (p.evaluation) {
          const red = p.evaluation.flags.red || [];
          const yellow = p.evaluation.flags.yellow || [];
          const green = p.evaluation.flags.green || [];

          if (red.length || yellow.length || green.length) {
            flagsHtml = `
              <div class="section-label">Health Intelligence Engine Flag Evaluation</div>
              <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px">
                ${red.map(f => `
                  <div class="health-preview red" style="margin:0;padding:8px 10px;border-radius:10px;">
                    <div class="prelabel" style="color:#b91c1c;font-weight:800;font-size:9px;margin-bottom:2px">🔴 High Concern (Red Flag) · ${f.concern || 'Clinical Alert'}</div>
                    <strong style="font-size:12px">${f.name}</strong>
                    <p style="font-size:11px;margin:2px 0 0;color:var(--ink-700)">${f.message}</p>
                    ${f.recommendation ? `<p style="font-size:10.5px;color:var(--ink-500);margin-top:3px">&rarr; Recommendation: ${f.recommendation}</p>` : ''}
                  </div>
                `).join('')}
                ${yellow.map(f => `
                  <div class="health-preview yellow" style="margin:0;padding:8px 10px;border-radius:10px;">
                    <div class="prelabel" style="color:#b45309;font-weight:800;font-size:9px;margin-bottom:2px">🟡 Caution (Yellow Flag) · ${f.concern || 'Dietary Sensitivity'}</div>
                    <strong style="font-size:12px">${f.name}</strong>
                    <p style="font-size:11px;margin:2px 0 0;color:var(--ink-700)">${f.message}</p>
                    ${f.recommendation ? `<p style="font-size:10.5px;color:var(--ink-500);margin-top:3px">&rarr; Recommendation: ${f.recommendation}</p>` : ''}
                  </div>
                `).join('')}
                ${green.map(f => `
                  <div class="health-preview green" style="margin:0;padding:8px 10px;border-radius:10px;">
                    <div class="prelabel" style="color:#15803d;font-weight:800;font-size:9px;margin-bottom:2px">🟢 Positive Attribute (Green Flag)</div>
                    <strong style="font-size:12px">${f.name}</strong>
                    <p style="font-size:11px;margin:2px 0 0;color:var(--ink-700)">${f.message}</p>
                  </div>
                `).join('')}
              </div>
            `;
          } else {
            flagsHtml = `
              <div class="section-label">Health Intelligence Engine Flag Evaluation</div>
              <div class="notice" style="margin-bottom:14px">✅ No risk flags triggered. All nutritional declarations fall within recommended baseline limits.</div>
            `;
          }
        }

        const imgDisplay = p.image_url
          ? `<img src="${p.image_url.startsWith('http') ? p.image_url : '{{ asset('') }}' + p.image_url.replace(/^\//, '')}" alt="${p.name}" onerror="this.style.display='none'; this.parentElement.textContent='${p.brand ? p.brand.substring(0, 2).toUpperCase() : 'PR'}';">`
          : (p.brand ? p.brand.substring(0, 2).toUpperCase() : 'PR');

        openModal({
          title: p.name,
          subtitle: `${p.brand} · Barcode: ${p.barcode}`,
          size: 'wide',
          body: `
            <div style="display:flex;gap:14px;align-items:center;margin-bottom:14px;padding:12px;background:var(--surface-subtle);border-radius:12px;border:1px solid var(--line)">
              <div class="thumb" style="width:58px;height:58px;border-radius:12px;font-size:16px;flex-shrink:0">
                ${imgDisplay}
              </div>
              <div style="min-width:0">
                <h4 style="margin:0;font-size:14px;font-weight:750;color:var(--ink-900)">${p.name}</h4>
                <div style="font-size:11px;color:var(--ink-500);margin-top:2px">${p.brand} · Barcode: <code>${p.barcode}</code></div>
              </div>
            </div>
            ${flagsHtml}
            <div class="grid grid-2">
              <div>
                <div class="section-label">General Specifications</div>
                <div class="control-list">
                  <div class="control-row"><div class="control-copy"><strong>Category</strong><p>${p.category}</p></div></div>
                  <div class="control-row"><div class="control-copy"><strong>Country</strong><p>${p.country || 'Not set'}</p></div></div>
                  <div class="control-row"><div class="control-copy"><strong>Manufacturer</strong><p>${p.manufacturer || 'Not set'}</p></div></div>
                  <div class="control-row"><div class="control-copy"><strong>Serving Size</strong><p>${p.serving_size || 'Not set'}</p></div></div>
                  <div class="control-row"><div class="control-copy"><strong>Source</strong><p>${p.source || 'Admin entry'}</p></div></div>
                </div>
              </div>
              <div>
                <div class="section-label">Nutrition Profile</div>
                <div class="grid grid-2">${nutritionHtml}</div>
              </div>
            </div>
            <div class="section-label">Ingredients Statement</div>
            <div class="notice">${p.ingredients || 'No ingredients statement provided.'}</div>
          `,
          footer: `
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>
            <button type="button" class="btn btn-primary" onclick="closeModal();editProduct(${p.id})">Edit Product</button>
            ${!p.verified ? `
              <form method="POST" action="{{ url('admin/products') }}/${p.id}/verify" style="display:inline;margin:0">
                @csrf
                <button type="submit" class="btn btn-success" style="background:#059669;color:#fff;border-color:#059669;gap:6px;display:inline-flex;align-items:center;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                  Verify &amp; Publish
                </button>
              </form>
            ` : ''}
          `
        });
      });
  }

  function editProduct(id) {
    fetch(`{{ url('admin/products') }}/${id}`)
      .then(res => res.json())
      .then(p => {
        const currentImgUrl = p.image_url
          ? (p.image_url.startsWith('http') ? p.image_url : '{{ asset('') }}' + p.image_url.replace(/^\//, ''))
          : '';

        openModal({
          title: 'Edit Product',
          subtitle: `${p.name} (Barcode: ${p.barcode})`,
          size: 'wide',
          body: `
            <form id="editProductForm" method="POST" action="{{ url('admin/products') }}/${p.id}" enctype="multipart/form-data">
              @csrf
              @method('PUT')
              <div class="form-grid">
                <div class="field full">
                  <label>Product Picture</label>
                  <div class="image-dropzone" id="editProductDropzone" onclick="document.getElementById('editProductImageInput').click()" ondragover="event.preventDefault(); this.classList.add('dragover');" ondragleave="this.classList.remove('dragover');" ondrop="event.preventDefault(); this.classList.remove('dragover'); handleProductImageDrop(event, 'editProductImageInput', 'editProductPreviewContainer', 'editProductDropzonePrompt');">
                    <input type="file" name="image" id="editProductImageInput" accept="image/png,image/jpeg,image/webp,image/jpg,image/gif" style="display:none" onchange="handleProductImagePreview(this, 'editProductPreviewContainer', 'editProductDropzonePrompt')">
                    <input type="hidden" name="remove_image" id="editRemoveImage" value="0">
                    <div id="editProductDropzonePrompt" style="${currentImgUrl ? 'display:none;' : 'display:flex;flex-direction:column;align-items:center;gap:6px;padding:6px 0;'}">
                      <div style="width:38px;height:38px;border-radius:10px;background:var(--green-50);display:grid;place-items:center;color:var(--green-700)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                      </div>
                      <div style="font-size:12px;font-weight:650;color:var(--ink-800)">Upload New Picture <span style="font-weight:normal;color:var(--ink-500)">(Click or drag image here)</span></div>
                      <div style="font-size:10px;color:var(--ink-400)">Supports PNG, JPG, WEBP or GIF up to 5MB.</div>
                    </div>
                    <div id="editProductPreviewContainer" style="${currentImgUrl ? 'display:block;width:100%' : 'display:none;width:100%'}">
                      ${currentImgUrl ? `
                        <div class="image-dropzone-preview">
                          <img src="${currentImgUrl}" alt="Current Product Image">
                          <div style="flex:1;min-width:0;text-align:left">
                            <strong style="font-size:11.5px;color:var(--ink-900);display:block">Current Product Picture</strong>
                            <span style="font-size:10px;color:var(--ink-500)">Click box to replace with new image</span>
                          </div>
                          <button type="button" class="btn btn-ghost btn-sm" style="color:var(--danger-strong);font-size:11px;padding:4px 8px;height:auto" onclick="event.stopPropagation(); removeExistingProductImage('editRemoveImage', 'editProductPreviewContainer', 'editProductDropzonePrompt')">
                            Remove
                          </button>
                        </div>
                      ` : ''}
                    </div>
                  </div>
                </div>
                <div class="field full">
                  <label>Product Name *</label>
                  <input name="name" class="input" value="${p.name || ''}">
                  <div class="product-name-error" style="display:none;color:#dc2626;font-size:11px;font-weight:600;margin-top:4px;"></div>
                </div>
                <div class="field">
                  <label>Brand *</label>
                  <input name="brand" class="input" value="${p.brand || ''}" required>
                </div>
                <div class="field">
                  <label>Barcode *</label>
                  <input name="barcode" class="input" value="${p.barcode || ''}" required>
                </div>
                <div class="field">
                  <label>Category *</label>
                  <select name="category" class="select" required>
                    @if(isset($categories))
                      @foreach($categories as $cat)
                        <option value="{{ $cat }}" ${p.category === '{{ $cat }}' ? 'selected' : ''}>{{ $cat }}</option>
                      @endforeach
                    @endif
                  </select>
                </div>
                <div class="field">
                  <label>Status *</label>
                  <select name="status" class="select" required>
                    <option value="Published" ${p.status === 'Published' ? 'selected' : ''}>Published</option>
                    <option value="Draft" ${p.status === 'Draft' ? 'selected' : ''}>Draft</option>
                    <option value="Archived" ${p.status === 'Archived' ? 'selected' : ''}>Archived</option>
                  </select>
                </div>
                <div class="field">
                  <label>Serving Size</label>
                  <input name="serving_size" class="input" value="${p.serving_size || ''}">
                </div>
                <div class="field">
                  <label>Manufacturer</label>
                  <input name="manufacturer" class="input" value="${p.manufacturer || ''}">
                </div>
                <div class="field full">
                  <label>Ingredients Statement</label>
                  <textarea name="ingredients" class="textarea">${p.ingredients || ''}</textarea>
                </div>
              </div>

              <div class="section-label" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
                <span>Nutritional Declaration (Evaluated by Health Intelligence Engine)</span>
                <div style="display:flex;align-items:center;gap:8px">
                  <button type="button" class="btn-add-nutrient" onclick="addNutrientBox(this)">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
                    Add Nutrient
                  </button>
                  <span class="nutrition-counter-badge" style="font-size:11.5px;font-weight:600;padding:3px 10px;border-radius:20px;"></span>
                </div>
              </div>
              <div style="font-size:12px;color:var(--ink-500);margin:-6px 0 12px 0;">
                Any 3 nutrient boxes are required for health re-evaluation. Emptied or removed boxes will be cleared from product profile.
              </div>
              <div class="nutrient-error-placeholder"></div>
              <div class="nutrient-grid">
                ${(() => {
                  const standardUnitMap = {
                    'sodium': { name: 'Sodium (mg)', placeholder: 'e.g. 210' },
                    'sugar': { name: 'Sugar (g)', placeholder: 'e.g. 12' },
                    'addedsugar': { name: 'Added Sugar (g)', placeholder: 'e.g. 5' },
                    'saturatedfat': { name: 'Saturated Fat (g)', placeholder: 'e.g. 4.5' },
                    'transfat': { name: 'Trans Fat (g)', placeholder: 'e.g. 0' },
                    'calories': { name: 'Calories (kcal)', placeholder: 'e.g. 150' },
                    'totalfat': { name: 'Total Fat (g)', placeholder: 'e.g. 8' },
                    'fat': { name: 'Total Fat (g)', placeholder: 'e.g. 8' },
                    'protein': { name: 'Protein (g)', placeholder: 'e.g. 3' },
                    'fiber': { name: 'Fiber (g)', placeholder: 'e.g. 4' },
                    'carbohydrate': { name: 'Carbohydrate (g)', placeholder: 'e.g. 20' }
                  };
                  const standardOrder = ['sodium', 'sugar', 'addedsugar', 'saturatedfat', 'transfat', 'calories', 'totalfat', 'fat', 'protein', 'fiber', 'carbohydrate'];

                  const boxesHtml = [];

                  // If product has nutrition entries saved, ONLY display those saved nutrients
                  if (p.nutrition && Object.keys(p.nutrition).length > 0) {
                    const entries = Object.entries(p.nutrition).filter(([k, v]) => v !== null && String(v).trim() !== '');
                    entries.sort(([aK], [bK]) => {
                      const cleanA = aK.toLowerCase().replace(/[\s_\-]/g, '');
                      const cleanB = bK.toLowerCase().replace(/[\s_\-]/g, '');
                      const idxA = standardOrder.indexOf(cleanA);
                      const idxB = standardOrder.indexOf(cleanB);
                      if (idxA !== -1 && idxB !== -1) return idxA - idxB;
                      if (idxA !== -1) return -1;
                      if (idxB !== -1) return 1;
                      return aK.localeCompare(bK);
                    });

                    entries.forEach(([rawK, rawV]) => {
                      const cleanK = rawK.toLowerCase().replace(/[\s_\-]/g, '');
                      let displayName = rawK;
                      let placeholder = 'e.g. 0';

                      if (standardUnitMap[cleanK]) {
                        displayName = standardUnitMap[cleanK].name;
                        placeholder = standardUnitMap[cleanK].placeholder;
                      } else {
                        const unitMatch = String(rawV).replace(/[\d\.]+/g, '').trim();
                        if (unitMatch && !displayName.includes('(')) {
                          displayName = `${rawK} (${unitMatch})`;
                        }
                      }

                      const numMatch = String(rawV).match(/[\d\.]+/);
                      const val = numMatch ? numMatch[0] : '';

                      boxesHtml.push(`
                        <div class="field nutrient-box">
                          <div class="nutrient-box-header">
                            <input type="text" name="nutrient_names[]" class="nutrient-name-input" value="${displayName}" placeholder="Nutrient Name (unit)" title="Click to edit nutrient name">
                            <button type="button" class="nutrient-remove-btn" title="Remove nutrient box" onclick="removeNutrientBox(this)">&times;</button>
                          </div>
                          <div class="nutrient-name-error" style="color:#dc2626;font-size:10px;font-weight:600;margin:2px 0 5px;display:none;"></div>
                          <input type="number" step="any" min="0" name="nutrient_values[]" class="input nutrient-value-input" value="${val}" placeholder="${placeholder}">
                        </div>
                      `);
                    });
                  } else {
                    // Only fallback to initial 8 default boxes if product has NO nutrition declared at all
                    const defaultDefs = [
                      { name: 'Sodium (mg)', placeholder: 'e.g. 210' },
                      { name: 'Sugar (g)', placeholder: 'e.g. 12' },
                      { name: 'Added Sugar (g)', placeholder: 'e.g. 5' },
                      { name: 'Saturated Fat (g)', placeholder: 'e.g. 4.5' },
                      { name: 'Trans Fat (g)', placeholder: 'e.g. 0' },
                      { name: 'Calories (kcal)', placeholder: 'e.g. 150' },
                      { name: 'Total Fat (g)', placeholder: 'e.g. 8' },
                      { name: 'Protein (g)', placeholder: 'e.g. 3' }
                    ];

                    defaultDefs.forEach(def => {
                      boxesHtml.push(`
                        <div class="field nutrient-box">
                          <div class="nutrient-box-header">
                            <input type="text" name="nutrient_names[]" class="nutrient-name-input" value="${def.name}" placeholder="Nutrient Name (unit)" title="Click to edit nutrient name">
                            <button type="button" class="nutrient-remove-btn" title="Remove nutrient box" onclick="removeNutrientBox(this)">&times;</button>
                          </div>
                          <div class="nutrient-name-error" style="color:#dc2626;font-size:10px;font-weight:600;margin:2px 0 5px;display:none;"></div>
                          <input type="number" step="any" min="0" name="nutrient_values[]" class="input nutrient-value-input" value="" placeholder="${def.placeholder}">
                        </div>
                      `);
                    });
                  }

                  return boxesHtml.join('');
                })()}
              </div>

              <div class="section-label">Verification</div>
              <div class="checkbox-row">
                <input type="checkbox" name="verified" value="1" id="editVerified" ${p.verified ? 'checked' : ''}>
                <label for="editVerified">Mark product information as verified after source review (automatically sets status to Published).</label>
              </div>
            </form>
          `,
          footer: `
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="submitProductForm('editProductForm', this)">Save & Re-Evaluate Product</button>
          `
        });
      });
  }

  function handleProductImagePreview(input, containerId, promptId) {
    const container = document.getElementById(containerId);
    const prompt = document.getElementById(promptId);
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    if (!file.type.startsWith('image/')) {
      alert('Please select an image file (PNG, JPG, WEBP, GIF).');
      input.value = '';
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      alert('The selected image exceeds the maximum allowed size of 5MB.');
      input.value = '';
      return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
      if (prompt) prompt.style.display = 'none';
      if (container) {
        container.style.display = 'block';
        container.innerHTML = `
          <div class="image-dropzone-preview">
            <img src="${e.target.result}" alt="Preview">
            <div style="flex:1;min-width:0;text-align:left">
              <strong style="font-size:11.5px;color:var(--ink-900);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${file.name}</strong>
              <span style="font-size:10px;color:var(--ink-500)">${(file.size / 1024).toFixed(1)} KB · Ready to save</span>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" style="color:var(--danger-strong);font-size:11px;padding:4px 8px;height:auto" onclick="event.stopPropagation(); clearProductImagePreview('${input.id}', '${containerId}', '${promptId}')">
              Remove
            </button>
          </div>
        `;
      }
    };
    reader.readAsDataURL(file);
  }

  function handleProductImageDrop(event, inputId, containerId, promptId) {
    const input = document.getElementById(inputId);
    if (event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files.length > 0) {
      input.files = event.dataTransfer.files;
      handleProductImagePreview(input, containerId, promptId);
    }
  }

  function clearProductImagePreview(inputId, containerId, promptId) {
    const input = document.getElementById(inputId);
    if (input) input.value = '';
    const container = document.getElementById(containerId);
    if (container) {
      container.innerHTML = '';
      container.style.display = 'none';
    }
    const prompt = document.getElementById(promptId);
    if (prompt) prompt.style.display = 'flex';
  }

  function removeExistingProductImage(removeInputId, containerId, promptId) {
    const removeInput = document.getElementById(removeInputId);
    if (removeInput) removeInput.value = '1';
    const container = document.getElementById(containerId);
    if (container) {
      container.innerHTML = '';
      container.style.display = 'none';
    }
    const prompt = document.getElementById(promptId);
    if (prompt) prompt.style.display = 'flex';
  }
</script>
