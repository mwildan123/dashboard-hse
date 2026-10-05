/* Dashboard HSE - perilaku form (tanpa dependensi) */
(function() {
    'use strict';

    var $ = function(sel, root) { return (root || document).querySelector(sel); };
    var $$ = function(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
    var pad = function(n) { return String(n).padStart(2, '0'); };

    var MAX_PHOTO_BYTES = 10 * 1024 * 1024;

    /* ---------- Toast ---------- */
    var toastEl = $('#toast');
    var toastTimer;

    function toast(message, tone) {
        if (!toastEl) return;
        toastEl.textContent = message;
        toastEl.dataset.tone = tone || 'info';
        toastEl.classList.add('is-visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function() { toastEl.classList.remove('is-visible'); }, 4200);
    }

    /* ---------- Nilai awal tanggal & jam ---------- */
    function todayValue() {
        var d = new Date();
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }

    function nowValue() {
        var d = new Date();
        return pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function applyDefaults(root) {
        $$('[data-default="today"]', root).forEach(function(el) { if (!el.value) el.value = todayValue(); });
        $$('[data-default="now"]', root).forEach(function(el) { if (!el.value) el.value = nowValue(); });
    }

    /* ---------- Foto: upload atau ambil dari kamera ---------- */
    function formatSize(bytes) {
        if (bytes < 1024 * 1024) return Math.max(1, Math.round(bytes / 1024)) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1).replace('.', ',') + ' MB';
    }

    function initPhoto(box) {
        var input = $('input[type="file"]', box);
        var empty = $('[data-photo-empty]', box);
        var preview = $('[data-photo-preview]', box);
        var img = $('img', preview);
        var nameEl = $('[data-photo-name]', box);
        var sizeEl = $('[data-photo-size]', box);
        var errorEl = $('[data-photo-error]', box);
        var objectUrl = null;

        function showError(message) {
            errorEl.textContent = message;
            errorEl.hidden = false;
            box.classList.add('has-error');
        }

        function clearError() {
            errorEl.hidden = true;
            box.classList.remove('has-error');
        }

        function clear() {
            input.value = '';
            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = null;
            img.removeAttribute('src');
            preview.hidden = true;
            empty.hidden = false;
            clearError();
        }

        input.addEventListener('change', function() {
            var file = input.files && input.files[0];
            clearError();
            if (!file) { clear(); return; }

            if (file.type && file.type.indexOf('image/') !== 0) {
                input.value = '';
                showError('File harus berupa gambar (JPG, PNG, atau HEIC).');
                return;
            }
            if (file.size > MAX_PHOTO_BYTES) {
                input.value = '';
                showError('Ukuran foto melebihi 10 MB. Ambil ulang dengan resolusi lebih kecil.');
                return;
            }

            if (objectUrl) URL.revokeObjectURL(objectUrl);
            objectUrl = URL.createObjectURL(file);
            img.src = objectUrl;
            nameEl.textContent = file.name;
            sizeEl.textContent = formatSize(file.size);
            empty.hidden = true;
            preview.hidden = false;
        });

        $$('[data-photo-camera]', box).forEach(function(btn) {
            btn.addEventListener('click', function() {
                input.setAttribute('capture', 'environment');
                input.click();
            });
        });

        $$('[data-photo-pick]', box).forEach(function(btn) {
            btn.addEventListener('click', function() {
                input.removeAttribute('capture');
                input.click();
            });
        });

        $$('[data-photo-clear]', box).forEach(function(btn) {
            btn.addEventListener('click', clear);
        });

        var form = box.closest('form');
        if (form) form.addEventListener('reset', function() { setTimeout(clear, 0); });
    }

    /* ---------- Plat nomor & masa berlaku SIM ---------- */
    function initPlate(input) {
        input.addEventListener('input', function() {
            var pos = input.selectionStart;
            input.value = input.value.toUpperCase();
            try { input.setSelectionRange(pos, pos); } catch (e) { /* abaikan */ }
        });
    }

    function initExpiry(input) {
        var note = $('[data-expiry-note]', input.closest('.field'));

        function check() {
            if (!note) return;
            note.hidden = !(input.value && input.value < todayValue());
        }
        input.addEventListener('change', check);
        input.addEventListener('input', check);
        var form = input.closest('form');
        if (form) form.addEventListener('reset', function() { setTimeout(check, 0); });
    }

    /* ---------- Form kendaraan: isian dinamis ---------- */
    function initVehicle(form) {
        var radios = $$('input[name="kendaraan"]', form);
        var groups = $$('[data-vehicle-group]', form);
        var empty = $('[data-vehicle-empty]', form);

        function update() {
            var picked = radios.filter(function(r) { return r.checked; })[0];
            var value = picked ? picked.value : '';

            groups.forEach(function(group) {
                var on = value !== '' && group.dataset.showFor.split(' ').indexOf(value) !== -1;
                group.hidden = !on;
                $$('input, select, textarea', group).forEach(function(el) { el.disabled = !on; });
            });
            if (empty) empty.hidden = value !== '';
        }

        radios.forEach(function(r) { r.addEventListener('change', update); });
        form.addEventListener('reset', function() { setTimeout(update, 0); });
        update();
    }

    /* ---------- Matriks checklist forklift ---------- */
    function initMatrix(form) {
        var config = window.FORKLIFT_CHECKLIST;
        var host = $('#matrix', form);
        if (!config || !host) return;

        var html = '<div class="mx-head" aria-hidden="true"><span>Item</span>' +
            config.options.map(function(o) { return '<span>' + o.label + '</span>'; }).join('') +
            '</div>';

        config.groups.forEach(function(group) {
            html += '<section class="mx-group" data-group="' + group.id + '">' +
                '<div class="mx-group-head"><h3>' + group.title + '</h3>' +
                '<span class="mx-count" data-count>0/' + group.items.length + '</span></div>';

            group.items.forEach(function(item) {
                var key = group.id + '_' + item[0];
                html += '<div class="mx-row" role="radiogroup" aria-labelledby="mx-' + key + '">' +
                    '<span class="mx-name" id="mx-' + key + '">' + item[1] + '</span>' +
                    '<div class="mx-opts">';
                config.options.forEach(function(o, i) {
                    html += '<label class="mx-opt" data-tone="' + o.tone + '">' +
                        '<input type="radio" name="pemeriksaan[' + key + ']" value="' + o.value + '"' + (i === 0 ? ' required' : '') + '>' +
                        '<span class="mx-mark"></span><span class="mx-text">' + o.label + '</span></label>';
                });
                html += '</div></div>';
            });
            html += '</section>';
        });
        host.innerHTML = html;

        var summary = $('#mx-summary');
        var hint = $('#catatan-hint');
        var total = config.groups.reduce(function(n, g) { return n + g.items.length; }, 0);

        function update() {
            var answered = 0,
                warn = 0,
                bad = 0;

            $$('.mx-group', host).forEach(function(group) {
                var rows = $$('.mx-row', group),
                    done = 0;
                rows.forEach(function(row) {
                    var checked = $('input:checked', row);
                    row.removeAttribute('data-flag');
                    if (!checked) return;
                    done++;
                    if (checked.value === 'perlu_perbaikan') {
                        warn++;
                        row.dataset.flag = 'warn';
                    }
                    if (checked.value === 'tidak_berfungsi') {
                        bad++;
                        row.dataset.flag = 'bad';
                    }
                });
                answered += done;
                var count = $('[data-count]', group);
                count.textContent = done + '/' + rows.length;
                count.classList.toggle('is-done', done === rows.length);
            });

            if (summary) {
                var text = '<strong>' + answered + ' dari ' + total + '</strong> item terisi';
                var issues = [];
                if (warn) issues.push(warn + ' perlu perbaikan');
                if (bad) issues.push(bad + ' tidak berfungsi');
                if (issues.length) text += '. ' + issues.join(', ').replace(/^./, function(c) { return c.toUpperCase(); }) + '.';
                summary.innerHTML = text;
            }

            if (hint) {
                hint.hidden = !(warn || bad);
                hint.textContent = 'Ada item yang perlu perbaikan atau tidak berfungsi. Tulis rinciannya di sini.';
            }
        }

        host.addEventListener('change', update);
        form.addEventListener('reset', function() { setTimeout(update, 0); });
        update();
    }

    /* ---------- Kirim form ---------- */
    function initForm(form) {
        var submitBtn = $('[type="submit"]', form);

        form.addEventListener('submit', function(event) {
            event.preventDefault();

            var endpoint = form.dataset.endpoint;
            var label = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Mengirim...';

            var finish = function() {
                submitBtn.disabled = false;
                submitBtn.textContent = label;
            };

            /* Mode pratinjau: belum ada endpoint, data tidak dikirim ke mana pun. */
            if (!endpoint) {
                setTimeout(function() {
                    if (window.console) console.info('[pratinjau] data form', Object.fromEntries(new FormData(form)));
                    toast('Pratinjau: data valid, belum dikirim ke server.', 'info');
                    finish();
                }, 400);
                return;
            }

            var csrf = $('meta[name="csrf-token"]');
            fetch(endpoint, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf ? csrf.content : ''
                }
            }).then(function(res) {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                toast(form.dataset.success || 'Data tersimpan', 'success');
                form.reset();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }).catch(function() {
                toast('Data belum terkirim. Periksa koneksi lalu coba lagi.', 'error');
            }).then(finish);
        });

        form.addEventListener('reset', function() { setTimeout(function() { applyDefaults(form); }, 0); });
    }

    function parseFilterDate(value) {
        if (!value) return null;
        var cleaned = String(value).trim();
        if (!cleaned) return null;

        if (/^\d{4}-\d{2}-\d{2}$/.test(cleaned)) {
            var iso = new Date(cleaned + 'T00:00:00');
            if (!isNaN(iso.getTime())) return iso;
        }

        var fallback = new Date(cleaned);
        if (!isNaN(fallback.getTime())) return fallback;

        var parts = cleaned.match(/^\d{1,2}\s+[A-Za-z]{3}\s+\d{4}$/);
        if (parts) {
            var alt = new Date(cleaned);
            if (!isNaN(alt.getTime())) return alt;
        }

        return null;
    }

    function normalizeFilterToken(value) {
        return String(value || '').replace(/^Panel\s+/i, '').replace(/\s+/g, '').toLowerCase();
    }

    function syncKwhChartWithDateRange() {
        var scopeEl = document.querySelector('[data-filter-scope="kwh"]');
        if (!scopeEl) return;

        var fromInput = $('#f_dari', scopeEl);
        var toInput = $('#f_sampai', scopeEl);
        var chartWrap = $('.chart-wrap');
        if (!chartWrap || !fromInput || !toInput) return;

        var points = $$('[data-chart-point]', chartWrap);
        if (!points.length) return;

        var update = function() {
            var fromDate = parseFilterDate(fromInput.value);
            var toDate = parseFilterDate(toInput.value);
            
            var minX = Infinity;
            var maxX = -Infinity;
            var uniqueDatesOrig = [];
            
            points.forEach(function(point) {
                var d = point.getAttribute('data-date');
                if (uniqueDatesOrig.indexOf(d) === -1) uniqueDatesOrig.push(d);
                
                var ox = parseFloat(point.getAttribute('data-original-x') || point.getAttribute('data-x'));
                if (!point.hasAttribute('data-original-x')) {
                    point.setAttribute('data-original-x', ox);
                }
                if (ox < minX) minX = ox;
                if (ox > maxX) maxX = ox;
            });
            
            var availableWidth = maxX - minX;
            if (availableWidth <= 0) availableWidth = 800;
            
            var visibleDates = [];
            uniqueDatesOrig.forEach(function(d) {
                var pointDate = parseFilterDate(d);
                var visible = true;
                if (fromDate && pointDate && pointDate < fromDate) visible = false;
                if (toDate && pointDate && pointDate > toDate) visible = false;
                if (visible) visibleDates.push(d);
            });
            
            var dateToNewX = {};
            if (visibleDates.length > 1) {
                var step = availableWidth / (visibleDates.length - 1);
                visibleDates.forEach(function(d, i) {
                    dateToNewX[d] = minX + (i * step);
                });
            } else if (visibleDates.length === 1) {
                dateToNewX[visibleDates[0]] = minX + (availableWidth / 2);
            }
            
            var emptyMsg = document.getElementById('chart-empty-msg');
            if (visibleDates.length === 0) {
                if (!emptyMsg) {
                    emptyMsg = document.createElement('div');
                    emptyMsg.id = 'chart-empty-msg';
                    emptyMsg.style.position = 'absolute';
                    emptyMsg.style.top = '45%';
                    emptyMsg.style.left = '50%';
                    emptyMsg.style.transform = 'translate(-50%, -50%)';
                    emptyMsg.style.color = '#94a3b8';
                    emptyMsg.style.fontSize = '0.9rem';
                    emptyMsg.style.textAlign = 'center';
                    chartWrap.style.position = 'relative';
                    chartWrap.appendChild(emptyMsg);
                }
                emptyMsg.innerHTML = 'Tidak ada data pada rentang tanggal ini.<br><small style="font-size: 0.8rem; opacity: 0.7;">(Grafik utama hanya memuat 14 hari terakhir)</small>';
                emptyMsg.style.display = 'block';
            } else if (emptyMsg) {
                emptyMsg.style.display = 'none';
            }
            
            // Sync all interactive points
            points.forEach(function(point) {
                var d = point.getAttribute('data-date');
                var visible = visibleDates.indexOf(d) !== -1;
                point.style.display = visible ? '' : 'none';
                if (visible && dateToNewX[d] !== undefined) {
                    point.setAttribute('data-x', dateToNewX[d]);
                    point.setAttribute('cx', dateToNewX[d]);
                }
            });
            
            // Sync visual dots
            var visualDots = $$('.chart-dot', chartWrap);
            visualDots.forEach(function(dot) {
                var d = dot.getAttribute('data-date');
                if (!d) return;
                var visible = visibleDates.indexOf(d) !== -1;
                dot.style.display = visible ? '' : 'none';
                if (visible && dateToNewX[d] !== undefined) {
                    dot.setAttribute('cx', dateToNewX[d]);
                }
            });
            
            // Sync axis labels
            var labels = $$('.chart-axis-label[data-date]', chartWrap);
            labels.forEach(function(label) {
                var d = label.getAttribute('data-date');
                if (!d) return;
                var visible = visibleDates.indexOf(d) !== -1;
                label.style.display = visible ? '' : 'none';
                if (visible && dateToNewX[d] !== undefined) {
                    label.setAttribute('x', dateToNewX[d]);
                }
            });

            var visiblePoints = points.filter(function(point) {
                return point.style.display !== 'none';
            });

            var lines = {
                utama: document.querySelector('.chart-line-1', chartWrap),
                office: document.querySelector('.chart-line-2', chartWrap)
            };
            
            var areas = {
                utama: document.querySelector('.chart-area-1', chartWrap),
                office: document.querySelector('.chart-area-2', chartWrap)
            };

            var baseline = 264; // pt + ch = 36 + (300 - 36 - 36)

            if (lines.utama) {
                var values = [];
                visiblePoints.forEach(function(point) {
                    if (!point.hasAttribute('data-y')) return;
                    var x = parseFloat(point.getAttribute('data-x'));
                    var y = parseFloat(point.getAttribute('data-y'));
                    if (!isNaN(x) && !isNaN(y)) values.push(x + ',' + y);
                });
                lines.utama.setAttribute('points', values.join(' '));
                
                if (areas.utama) {
                    if (values.length > 0) {
                        var firstX = values[0].split(',')[0];
                        var lastX = values[values.length - 1].split(',')[0];
                        areas.utama.setAttribute('d', "M" + firstX + "," + baseline + " L" + values.join(' L') + " L" + lastX + "," + baseline + " Z");
                    } else {
                        areas.utama.setAttribute('d', "");
                    }
                }
            }

            if (lines.office) {
                var values2 = [];
                visiblePoints.forEach(function(point) {
                    if (!point.hasAttribute('data-y-office')) return;
                    var x = parseFloat(point.getAttribute('data-x'));
                    var y = parseFloat(point.getAttribute('data-y-office'));
                    if (!isNaN(x) && !isNaN(y)) values2.push(x + ',' + y);
                });
                lines.office.setAttribute('points', values2.join(' '));
                
                if (areas.office) {
                    if (values2.length > 0) {
                        var firstX2 = values2[0].split(',')[0];
                        var lastX2 = values2[values2.length - 1].split(',')[0];
                        areas.office.setAttribute('d', "M" + firstX2 + "," + baseline + " L" + values2.join(' L') + " L" + lastX2 + "," + baseline + " Z");
                    } else {
                        areas.office.setAttribute('d', "");
                    }
                }
            }
        };

        update();
    }

    function applyTableFilter(scope) {
        var scopeEl = document.querySelector('[data-filter-scope="' + scope + '"]');
        if (!scopeEl) return;

        // Note: rows are queried fresh inside run() to handle AJAX-updated DOM

        var fromInput = $('#f_dari', scopeEl);
        var toInput = $('#f_sampai', scopeEl);
        var panelInput = $('#f_panel', scopeEl);
        var statusInput = $('#f_status', scopeEl);
        var jenisInput = $('#f_jenis', scopeEl);
        var searchInput = $('#f_cari', scopeEl);
        var unitInput = $('#f_unit', scopeEl);
        var deptInput = $('#f_dept', scopeEl);
        var operatorInput = $('#f_operator', scopeEl);
        var bulanTahunInput = $('#f_bulan_tahun', scopeEl);

        function run() {
            // Re-query rows from live DOM each time filter runs
            var rows = $$('[data-filter-row="' + scope + '"]');
            if (!rows.length) return;
            var fromValue = fromInput ? fromInput.value : '';
            var toValue = toInput ? toInput.value : '';
            var panelValue = panelInput ? panelInput.value.trim() : '';
            var statusValue = statusInput ? statusInput.value.trim() : '';
            var jenisValue = jenisInput ? jenisInput.value.trim() : '';
            var searchValue = searchInput ? searchInput.value.trim().toLowerCase() : '';
            var unitValue = unitInput ? unitInput.value.trim() : '';
            var deptValue = deptInput ? deptInput.value.trim() : '';
            var operatorValue = operatorInput ? operatorInput.value.trim() : '';
            var bulanTahunValue = bulanTahunInput ? bulanTahunInput.value.trim() : '';
            
            var bulanValue = '';
            var tahunValue = '';
            if (bulanTahunValue) {
                var parts = bulanTahunValue.split('-');
                if (parts.length === 2) {
                    tahunValue = parts[0];
                    var months = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'];
                    var monthIndex = parseInt(parts[1], 10) - 1;
                    if (monthIndex >= 0 && monthIndex < 12) {
                        bulanValue = months[monthIndex];
                    }
                }
            }
            
            var fromDate = parseFilterDate(fromValue);
            var toDate = parseFilterDate(toValue);
            var normalizedPanelValue = normalizeFilterToken(panelValue);

            var kwData = {};
            var totalRecords = 0;
            var totalKwh = 0;
            var totalInputRecords = 0;
            var totalInputKw = 0;

            // Tracking for Kendaraan Chart
            var totalGoodSim = 0;
            var totalBadSim = 0;
            var totalEmptySim = 0;
            var goodSimList = [];
            var badSimList = [];
            var emptySimList = [];
            var totalVehicles = 0;
            var motorCount = 0;
            var mobilCount = 0;
            var bothCount = 0;
            var warnCount = 0;

            rows.forEach(function(row) {
                var rowDate = row.getAttribute('data-date') || '';
                var rowPanel = (row.getAttribute('data-panel') || '').trim();
                var rowStatus = (row.getAttribute('data-status') || '').trim();
                var rowJenis = (row.getAttribute('data-jenis') || '').trim();
                var rowUnit = (row.getAttribute('data-unit') || '').trim();
                var rowDept = (row.getAttribute('data-dept') || '').trim();
                var rowOperator = (row.getAttribute('data-operator') || '').trim();
                var rowSearch = ((row.getAttribute('data-nama') || '') + ' ' + (row.getAttribute('data-plat') || '')).trim().toLowerCase();
                
                var rowBulan = (row.getAttribute('data-bulan') || '').toLowerCase(); 
                var rowTahun = (row.getAttribute('data-tahun') || ''); 
                
                var panelLower = rowPanel.toLowerCase();
                var isUtamaOrOffice = (panelLower === 'utama' || panelLower === 'office' || panelLower === 'utama, office');
                var isMonthlyRow = !isUtamaOrOffice;
                
                var matches = true;

                if (fromValue) {
                    if (!rowDate) {
                        matches = false;
                    } else {
                        var rowDateValue = parseFilterDate(rowDate);
                        if (rowDateValue && fromDate) {
                            if (isMonthlyRow) {
                                // Compare Year and Month only
                                var rowYM = rowDateValue.getFullYear() * 100 + rowDateValue.getMonth();
                                var fromYM = fromDate.getFullYear() * 100 + fromDate.getMonth();
                                if (rowYM < fromYM) matches = false;
                            } else {
                                if (rowDateValue < fromDate) matches = false;
                            }
                        }
                    }
                }

                if (matches && toValue) {
                    if (!rowDate) {
                        matches = false;
                    } else {
                        var rowDateValue = parseFilterDate(rowDate);
                        if (rowDateValue && toDate) {
                            if (isMonthlyRow) {
                                var rowYM = rowDateValue.getFullYear() * 100 + rowDateValue.getMonth();
                                var toYM = toDate.getFullYear() * 100 + toDate.getMonth();
                                if (rowYM > toYM) matches = false;
                            } else {
                                if (rowDateValue > toDate) matches = false;
                            }
                        }
                    }
                }

                if (matches && panelValue) {
                    var nRowPanel = normalizeFilterToken(rowPanel);
                    if (nRowPanel !== normalizedPanelValue && nRowPanel.indexOf(normalizedPanelValue) === -1) {
                        matches = false;
                    }
                }
                if (matches && statusValue && rowStatus.toLowerCase() !== statusValue.toLowerCase()) matches = false;
                if (matches && jenisValue && rowJenis.toLowerCase() !== jenisValue.toLowerCase()) matches = false;
                if (matches && searchValue && rowSearch.indexOf(searchValue) === -1) matches = false;
                if (matches && unitValue && rowUnit !== unitValue) matches = false;
                if (matches && deptValue && rowDept !== deptValue) matches = false;
                if (matches && operatorValue && rowOperator !== operatorValue) matches = false;
                if (matches && bulanValue && rowBulan !== bulanValue) matches = false;
                if (matches && tahunValue && rowTahun !== tahunValue) matches = false;

                // Simple fallback to filter by month if they type month in search, or from Date range
                row.hidden = !matches;
                
                // Track for Kendaraan Chart
                if (matches && scope === 'kendaraan') {
                    var nama = (row.getAttribute('data-nama') || '').trim();
                    var plat = (row.getAttribute('data-plat') || '').trim();
                    var jenisVeh = rowJenis.toLowerCase();
                    
                    totalVehicles++;
                    if (jenisVeh === 'motor') motorCount++;
                    else if (jenisVeh === 'mobil') mobilCount++;
                    else if (jenisVeh === 'motor & mobil') bothCount++;
                    
                    if (row.classList.contains('row-warn') || rowStatus === 'Peringatan') warnCount++;

                    if (rowStatus === 'SIM Aktif') {
                        totalGoodSim++;
                        goodSimList.push({ nama: nama, plat: plat });
                    } else if (rowStatus === 'Belum Mengisi') {
                        totalEmptySim++;
                        emptySimList.push({ nama: nama, plat: plat });
                    } else {
                        totalBadSim++;
                        badSimList.push({ nama: nama, plat: plat });
                    }
                }
                
                // Kalkulasi stat card khusus scope KWH dan memastikan hanya mengambil dari table-pencatatan untuk menghindari double-count
                if (matches && scope === 'kwh' && row.closest('#table-pencatatan')) {
                    totalRecords++;
                    var ut = parseFloat(row.getAttribute('data-c-utama')) || 0;
                    var off = parseFloat(row.getAttribute('data-c-office')) || 0;
                    totalKwh += (ut + off);
                }

                if (matches && scope === 'input_monitoring') {
                    totalInputRecords++;
                    var m = rowPanel || 'N/A';
                    
                    var val = parseFloat(row.getAttribute('data-kw')) || 0;
                    totalInputKw += val;

                    // Exclude Utama and Office from the Bar Chart
                    var mLower = m.toLowerCase();
                    if (mLower !== 'utama' && mLower !== 'office' && mLower !== 'utama, office') {
                        if (!kwData[m]) kwData[m] = 0;
                        kwData[m] += val;
                    }
                }
            });

            if (scope === 'kwh') {
                syncKwhChartWithDateRange();
                
                // Update elemen teks stat card
                var elRecord = document.getElementById('stat-total-record');
                var elKwh = document.getElementById('stat-total-kwh');
                var elRata = document.getElementById('stat-rata-rata');
                
                if (elRecord) {
                    elRecord.innerHTML = totalRecords;
                }
                
                if (elKwh) {
                    var kwhFormatted = totalKwh.toLocaleString('id-ID', { maximumFractionDigits: 0 });
                    elKwh.innerHTML = kwhFormatted + ' <small>kWh</small>';
                }
                
                if (elRata) {
                    var avg = totalRecords > 0 ? (totalKwh / totalRecords) : 0;
                    var avgFormatted = avg.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
                    elRata.innerHTML = avgFormatted + ' <small>kWh/hari</small>';
                }
            }
            if (scope === 'input_monitoring') {
                // Update elemen teks stat card untuk Input Monitoring
                var elInputRecord = document.getElementById('stat-input-record');
                var elInputKw = document.getElementById('stat-input-kw');
                var elInputAvg = document.getElementById('stat-input-avg');
                
                if (elInputRecord) elInputRecord.innerHTML = totalInputRecords;
                if (elInputKw) {
                    elInputKw.innerHTML = totalInputKw.toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' <small>KW</small>';
                }
                if (elInputAvg) {
                    var avgInput = totalInputRecords > 0 ? (totalInputKw / totalInputRecords) : 0;
                    elInputAvg.innerHTML = avgInput.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) + ' <small>KW/record</small>';
                }

                // Update new KW bar chart
                var chartWrapper = document.getElementById('kw-chart-wrapper');
                if (chartWrapper) {
                    var chartArr = Object.keys(kwData).map(function(k) { return { machine: k, kw: kwData[k] }; });
                    chartArr.sort(function(a, b) { return b.kw - a.kw; }); // Sort descending
                    chartArr = chartArr.slice(0, 15); // Take top 15
                    
                    var chartMax = chartArr.length ? chartArr[0].kw : 1;
                    if (chartMax <= 0) chartMax = 1;
                    
                    var html = '';
                    if (chartArr.length === 0) {
                        html = '<div style="text-align:center; padding: 2rem; color: #94a3b8; font-size: 0.9rem;">Tidak ada data untuk filter ini.</div>';
                    } else {
                        chartArr.forEach(function(bar, index) {
                            var pct = Math.min(100, Math.max(2, (bar.kw / chartMax) * 100));
                            var formattedKw = bar.kw.toLocaleString('id-ID', { minimumFractionDigits: 3, maximumFractionDigits: 3 });
                            
                            // Variasi warna subtle untuk bar pertama (tertinggi) agar sedikit berbeda (accent)
                            var gradient = index === 0 
                                ? 'linear-gradient(90deg, #1e40af, #2563eb)' // Darker royal blue for top 1
                                : 'linear-gradient(90deg, #2563eb, #38bdf8)'; // Standard blue to sky for others
                            
                            html += '<div style="display: flex; align-items: center; gap: 0.85rem; padding: 2px 0;">';
                            html += '<span style="min-width: 140px; font-size: 0.85rem; font-weight: 500; color: #334155; text-align: right; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' + bar.machine + '</span>';
                            
                            // Track (Background bar)
                            html += '<div style="flex: 1; height: 24px; background: #f8fafc; border-radius: 4px; border: 1px solid #f1f5f9; box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">';
                            
                            // Fill (Active bar)
                            html += '<div style="height: 100%; width: ' + pct + '%; background: ' + gradient + '; border-radius: 3px; box-shadow: 0 1px 2px rgba(37, 99, 235, 0.2); transition: width 0.8s cubic-bezier(0.2, 0.8, 0.2, 1); position: relative;">';
                            
                            html += '</div></div>';
                            html += '<span style="min-width: 95px; font-size: 0.8rem; font-weight: 600; color: #475569;">' + formattedKw + ' KW</span>';
                            html += '</div>';
                        });
                    }
                    chartWrapper.innerHTML = html;
                }
            }

            if (scope === 'kendaraan') {
                // Update Stat Cards
                var statCards = document.querySelectorAll('.stat-value');
                if (statCards.length >= 1) statCards[0].textContent = totalVehicles;
                if (statCards.length >= 2) statCards[1].innerHTML = motorCount + ' <small>/ ' + mobilCount + ' / ' + bothCount + '</small>';
                if (statCards.length >= 3) statCards[2].textContent = warnCount;

                var totalVeh = totalGoodSim + totalBadSim;
                var boxGood = document.getElementById('summary-box-good');
                var boxBad = document.getElementById('summary-box-bad');

                if (boxGood) {
                    var pctGood = totalVeh ? ((totalGoodSim / totalVeh) * 100) : 0;
                    var goodCountEl = boxGood.querySelector('.summary-count');
                    var goodBarEl = boxGood.querySelector('.summary-bar');
                    var goodListEl = boxGood.querySelector('.summary-list');
                    
                    if (goodCountEl) goodCountEl.textContent = totalGoodSim;
                    if (goodBarEl) {
                        goodBarEl.setAttribute('data-width', pctGood);
                        goodBarEl.style.width = pctGood + '%';
                    }
                    if (goodListEl) {
                        if (goodSimList.length === 0) {
                            goodListEl.innerHTML = '<li class="empty">Belum ada data</li>';
                        } else {
                            goodListEl.innerHTML = goodSimList.map(function(p) { return '<li><span>' + p.nama + '</span><span>' + p.plat + '</span></li>'; }).join('');
                        }
                    }
                }
                
                var boxSlate = document.getElementById('summary-box-slate');
                if (boxSlate) {
                    var pctEmpty = totalVeh ? ((totalEmptySim / totalVeh) * 100) : 0;
                    var emptyCountEl = boxSlate.querySelector('.summary-count');
                    var emptyBarEl = boxSlate.querySelector('.summary-bar');
                    var emptyListEl = boxSlate.querySelector('.summary-list');
                    
                    if (emptyCountEl) emptyCountEl.textContent = totalEmptySim;
                    if (emptyBarEl) {
                        emptyBarEl.setAttribute('data-width', pctEmpty);
                        emptyBarEl.style.width = pctEmpty + '%';
                    }
                    if (emptyListEl) {
                        if (emptySimList.length === 0) {
                            emptyListEl.innerHTML = '<li class="empty">Belum ada data</li>';
                        } else {
                            emptyListEl.innerHTML = emptySimList.map(function(p) { return '<li><span>' + p.nama + '</span><span>' + p.plat + '</span></li>'; }).join('');
                        }
                    }
                }

                if (boxBad) {
                    var pctBad = totalVeh ? ((totalBadSim / totalVeh) * 100) : 0;
                    var badCountEl = boxBad.querySelector('.summary-count');
                    var badBarEl = boxBad.querySelector('.summary-bar');
                    var badListEl = boxBad.querySelector('.summary-list');
                    
                    if (badCountEl) badCountEl.textContent = totalBadSim;
                    if (badBarEl) {
                        badBarEl.setAttribute('data-width', pctBad);
                        badBarEl.style.width = pctBad + '%';
                    }
                    if (badListEl) {
                        if (badSimList.length === 0) {
                            badListEl.innerHTML = '<li class="empty">Belum ada data</li>';
                        } else {
                            badListEl.innerHTML = badSimList.map(function(p) { return '<li><span>' + p.nama + '</span><span>' + p.plat + '</span></li>'; }).join('');
                        }
                    }
                }
            }
        }

        var trigger = document.querySelector('[data-filter-trigger="' + scope + '"]', scopeEl);
        if (trigger) trigger.addEventListener('click', run);
        
        var debounceTimer;
        var debouncedRun = function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(run, 150);
        };

        [fromInput, toInput, panelInput, statusInput, jenisInput, searchInput, unitInput, deptInput, operatorInput].forEach(function(input) {
            if (!input) return;
            // Gunakan event change untuk tanggal & select agar tidak memicu run terlalu sering saat belum selesai pilih
            if (input.type === 'date' || input.tagName === 'SELECT') {
                input.addEventListener('change', run);
            } else {
                input.addEventListener('input', debouncedRun);
            }
        });

        run();
    }

    /* ---------- Init ---------- */
    $$('[data-photo]').forEach(initPhoto);
    $$('[data-plate]').forEach(initPlate);
    $$('[data-sim-expiry]').forEach(initExpiry);
    ['kwh', 'kendaraan', 'input_monitoring'].forEach(applyTableFilter);

    $$('form[data-endpoint]').forEach(function(form) {
        applyDefaults(form);
        initForm(form);
        if (form.id === 'form-kendaraan') initVehicle(form);
        if (form.id === 'form-forklift') initMatrix(form);
    });

    /* ---------- Interaksi Sinkronisasi (Sync Info) ---------- */
    $$('.sync-info').forEach(function(el) {
        el.setAttribute('title', 'Klik untuk menyinkronkan ulang data');
        el.addEventListener('click', function() {
            if (el.classList.contains('is-syncing')) return;
            el.classList.add('is-syncing');
            toast('Menyinkronkan data monitoring...', 'info');

            setTimeout(function() {
                var strong = $('strong', el);
                var now = new Date();
                var months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                var timeStr = pad(now.getDate()) + ' ' + months[now.getMonth()] + ' ' + now.getFullYear() + ', ' + pad(now.getHours()) + ':' + pad(now.getMinutes());
                if (strong) strong.textContent = timeStr;
                el.classList.remove('is-syncing');
                toast('Data berhasil diperbarui!', 'good');
            }, 900);
        });
    });

    /* ---------- Tooltip Interaktif Grafik kWh (Hover & Klik) ---------- */
    function initChartTooltip() {
        var wrap = $('.chart-wrap');
        var tooltip = $('#chart-tooltip');
        if (!wrap || !tooltip) return;

        var ttDate = $('#tt-date', tooltip);
        var ttUtama = $('#tt-utama', tooltip);
        var ttOffice = $('#tt-office', tooltip);
        var ttTotal = $('#tt-total', tooltip);
        var allHits = $$('[data-chart-point]', wrap);
        var allDots = $$('.chart-dot', wrap);

        function showTooltip(hit) {
            var date = hit.getAttribute('data-date');
            var utama = hit.getAttribute('data-utama');
            var office = hit.getAttribute('data-office');
            var total = hit.getAttribute('data-total');
            var targetDotId = hit.getAttribute('data-target-dot');

            if (ttDate) ttDate.textContent = date;
            if (ttUtama) ttUtama.textContent = utama + ' kWh';
            if (ttOffice) ttOffice.textContent = office + ' kWh';
            if (ttTotal) ttTotal.textContent = total + ' kWh';

            var wrapRect = wrap.getBoundingClientRect();
            var hitRect = hit.getBoundingClientRect();
            var posX = hitRect.left + (hitRect.width / 2) - wrapRect.left;
            var posY = hitRect.top + (hitRect.height / 2) - wrapRect.top;

            // Flip ke bawah jika titik terlalu dekat dengan tepi atas grafik
            if (posY < 95) {
                tooltip.classList.add('is-flipped');
            } else {
                tooltip.classList.remove('is-flipped');
            }

            // Batasi agar tidak terpotong tepi kiri / kanan layar
            var ttWidth = tooltip.offsetWidth || 180;
            var halfW = ttWidth / 2;
            var minX = halfW + 6;
            var maxX = wrapRect.width - halfW - 6;
            var clampedX = Math.max(minX, Math.min(maxX, posX));

            // Geser panah penunjuk agar selalu tepat mengarah ke titik
            var arrowOffset = posX - clampedX;
            tooltip.style.setProperty('--arrow-offset', arrowOffset + 'px');

            tooltip.style.left = clampedX + 'px';
            tooltip.style.top = posY + 'px';
            tooltip.classList.add('is-visible');
            var cursorLine = $('#chart-cursor', wrap);
            var dataX = hit.getAttribute('data-x');
            if (cursorLine && dataX) {
                cursorLine.setAttribute('x1', dataX);
                cursorLine.setAttribute('x2', dataX);
                cursorLine.style.display = 'block';
            }
        }

        function hideTooltip() {
            tooltip.classList.remove('is-visible');
            tooltip.setAttribute('aria-hidden', 'true');
            var cursorLine = $('#chart-cursor', wrap);
            if (cursorLine) cursorLine.style.display = 'none';
        }

        allHits.forEach(function(hit) {
            hit.addEventListener('mouseenter', function() { showTooltip(hit); });
            hit.addEventListener('click', function(e) {
                e.stopPropagation();
                showTooltip(hit);
            });
            hit.addEventListener('focus', function() { showTooltip(hit); });
        });

        wrap.addEventListener('mouseleave', hideTooltip);

        document.addEventListener('click', function(e) {
            if (!wrap.contains(e.target)) {
                hideTooltip();
            }
        });
    }

    initChartTooltip();

    /* ---------- Table Pagination (15 rows per page) ---------- */
    window.initTablePagination = function(tableId, pagContainerId, perPage) {
        var table = document.getElementById(tableId);
        var pagContainer = document.getElementById(pagContainerId);
        if (!table || !pagContainer) return;

        var tbody = table.querySelector('tbody');
        if (!tbody) return;

        perPage = perPage || 15;
        var currentPage = 1;

        function getVisibleRows() {
            var allRows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
            return allRows.filter(function(row) {
                // Include rows that are not hidden by filter (hidden attribute) 
                // but treat pagination display separately
                return !row.hasAttribute('data-filter-hidden');
            });
        }

        function getAllRows() {
            return Array.prototype.slice.call(tbody.querySelectorAll('tr'));
        }

        function render() {
            var allRows = getAllRows();
            
            // First, mark rows hidden by filter vs pagination
            allRows.forEach(function(row) {
                if (row.hidden && !row.hasAttribute('data-pag-hidden')) {
                    row.setAttribute('data-filter-hidden', '');
                }
            });

            var visibleRows = allRows.filter(function(row) {
                return !row.hasAttribute('data-filter-hidden');
            });

            var totalRows = visibleRows.length;
            var totalPages = Math.max(1, Math.ceil(totalRows / perPage));

            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            var startIdx = (currentPage - 1) * perPage;
            var endIdx = startIdx + perPage;

            // Show/hide rows based on pagination
            visibleRows.forEach(function(row, idx) {
                if (idx >= startIdx && idx < endIdx) {
                    row.hidden = false;
                    row.removeAttribute('data-pag-hidden');
                } else {
                    row.hidden = true;
                    row.setAttribute('data-pag-hidden', '');
                }
            });

            // Build pagination controls
            if (totalPages <= 1) {
                pagContainer.innerHTML = '';
                return;
            }

            var html = '';

            // Prev button
            html += '<button class="pag-btn" data-pag-action="prev"' + (currentPage <= 1 ? ' disabled' : '') + '>';
            html += '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>';
            html += '</button>';

            // Page numbers (show max 5 pages around current)
            var startPage = Math.max(1, currentPage - 2);
            var endPage = Math.min(totalPages, startPage + 4);
            if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

            if (startPage > 1) {
                html += '<button class="pag-btn" data-pag-page="1">1</button>';
                if (startPage > 2) html += '<span class="pag-info">...</span>';
            }

            for (var p = startPage; p <= endPage; p++) {
                html += '<button class="pag-btn' + (p === currentPage ? ' is-active' : '') + '" data-pag-page="' + p + '">' + p + '</button>';
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) html += '<span class="pag-info">...</span>';
                html += '<button class="pag-btn" data-pag-page="' + totalPages + '">' + totalPages + '</button>';
            }

            // Next button
            html += '<button class="pag-btn" data-pag-action="next"' + (currentPage >= totalPages ? ' disabled' : '') + '>';
            html += '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>';
            html += '</button>';

            // Info
            html += '<span class="pag-info">Halaman ' + currentPage + ' dari ' + totalPages + ' (' + totalRows + ' data)</span>';

            pagContainer.innerHTML = html;

            // Attach events
            pagContainer.querySelectorAll('[data-pag-page]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    currentPage = parseInt(btn.getAttribute('data-pag-page'));
                    render();
                });
            });

            var prevBtn = pagContainer.querySelector('[data-pag-action="prev"]');
            var nextBtn = pagContainer.querySelector('[data-pag-action="next"]');
            if (prevBtn) prevBtn.addEventListener('click', function() { if (currentPage > 1) { currentPage--; render(); } });
            if (nextBtn) nextBtn.addEventListener('click', function() { if (currentPage < totalPages) { currentPage++; render(); } });
        }

        // Initial render
        render();

        // Re-render pagination when filters change
        return { refresh: function() { 
            getAllRows().forEach(function(row) {
                // If it's currently hidden, it means the filter hid it
                // (because run() unconditionally sets row.hidden = !matches)
                if (row.hidden) {
                    row.setAttribute('data-filter-hidden', '');
                } else {
                    row.removeAttribute('data-filter-hidden');
                }
                // Clear any leftover pagination markers so render() can start fresh
                row.removeAttribute('data-pag-hidden');
            });
            currentPage = 1;
            render();
        }};
    }

    // Initialize pagination for all tables
    var pagFoto = window.initTablePagination('table-foto', 'pag-foto', 15);
    var pagPencatatan = window.initTablePagination('table-pencatatan', 'pag-pencatatan', 15);
    var pagInput = window.initTablePagination('table-input', 'pag-input', 15);
    var pagKendaraan = window.initTablePagination('table-kendaraan', 'pag-kendaraan', 15);

    // Hook pagination refresh into filter system
    var origFilterTrigger = document.querySelector('[data-filter-trigger="kwh"]');
    if (origFilterTrigger) {
        origFilterTrigger.addEventListener('click', function() {
            setTimeout(function() {
                if (pagFoto) pagFoto.refresh();
                if (pagPencatatan) pagPencatatan.refresh();
            }, 200);
        });
    }

    var inputFilterTrigger = document.querySelector('[data-filter-trigger="input_monitoring"]');
    if (inputFilterTrigger) {
        inputFilterTrigger.addEventListener('click', function() {
            setTimeout(function() {
                if (pagInput) pagInput.refresh();
            }, 200);
        });
    }

    var kendFilterTrigger = document.querySelector('[data-filter-trigger="kendaraan"]');
    if (kendFilterTrigger) {
        kendFilterTrigger.addEventListener('click', function() {
            setTimeout(function() {
                if (pagKendaraan) pagKendaraan.refresh();
            }, 200);
        });
    }
    
    // Auto-refresh hook for ajax updates
    window.refreshKendaraanPag = function() {
        if (pagKendaraan) pagKendaraan.refresh();
    };

    // Also refresh pagination on filter input changes
    ['f_dari', 'f_sampai'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function() {
                setTimeout(function() {
                    if (pagFoto) pagFoto.refresh();
                    if (pagPencatatan) pagPencatatan.refresh();
                }, 200);
            });
        }
    });

    ['f_bulan_tahun', 'f_panel'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function() {
                setTimeout(function() {
                    if (pagInput) pagInput.refresh();
                }, 200);
            });
        }
    });

    function downloadPdf(reportTitle) {
        // Jika bukan halaman konsumsi KWH, langsung print saja
        if (!reportTitle || !reportTitle.includes('Konsumsi-kWh')) {
            var prevTitle = document.title;
            var today = new Date().toISOString().slice(0, 10);
            var cleanTitle = (reportTitle || 'Laporan-HSE-PCI') + '_' + today;
            document.title = cleanTitle;
            toast('Membuka pratinjau cetak PDF...', 'info');
            setTimeout(function() {
                window.print();
                setTimeout(function() {
                    document.title = prevTitle;
                }, 1000);
            }, 300);
            return;
        }

        var overlay = document.createElement('div');
        overlay.style = 'position:fixed;inset:0;background:rgba(15,39,71,0.6);backdrop-filter:blur(4px);z-index:9999;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity 0.2s;';
        
        var modal = document.createElement('div');
        modal.style = 'background:#fff;border-radius:12px;padding:24px;width:340px;box-shadow:0 12px 32px rgba(0,0,0,0.2);transform:translateY(20px);transition:transform 0.2s; font-family:var(--font, sans-serif);';
        
        modal.innerHTML = `
            <h3 style="margin-top:0;margin-bottom:16px;font-size:18px;color:#0F2039;font-weight:600;">Pilih Bagian Cetak (PDF)</h3>
            <div style="display:flex;flex-direction:column;gap:12px;margin-bottom:24px;font-size:15px;color:#44566F;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;"><input type="radio" name="pdf_part" value="all" checked style="width:18px;height:18px;"> Semua Bagian</label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;"><input type="radio" name="pdf_part" value="panel-tren" style="width:18px;height:18px;"> Tren Konsumsi (Grafik)</label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;"><input type="radio" name="pdf_part" value="panel-pencatatan" style="width:18px;height:18px;"> Data Pencatatan Terbaru</label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;"><input type="radio" name="pdf_part" value="panel-input" style="width:18px;height:18px;"> Data Input Monitoring</label>
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <button type="button" id="btnCancelPdf" style="padding:10px 18px;border:1px solid #B7C5DB;background:#fff;border-radius:8px;cursor:pointer;font-size:14px;font-weight:500;color:#0F2039;">Batal</button>
                <button type="button" id="btnConfirmPdf" style="padding:10px 18px;border:none;background:#2563EB;color:#fff;border-radius:8px;cursor:pointer;font-size:14px;font-weight:600;box-shadow:0 3px 8px rgba(37,99,235,0.35);">Download</button>
            </div>
        `;
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        requestAnimationFrame(function() {
            overlay.style.opacity = '1';
            modal.style.transform = 'translateY(0)';
        });
        
        function close() {
            overlay.style.opacity = '0';
            modal.style.transform = 'translateY(20px)';
            setTimeout(function() { overlay.remove(); }, 200);
        }
        
        modal.querySelector('#btnCancelPdf').onclick = close;
        modal.querySelector('#btnConfirmPdf').onclick = function() {
            var selected = modal.querySelector('input[name="pdf_part"]:checked').value;
            close();
            
            document.body.setAttribute('data-print-part', selected);
            
            var prevTitle = document.title;
            var today = new Date().toISOString().slice(0, 10);
            var cleanTitle = (reportTitle || 'Laporan-HSE-PCI') + '_' + today;
            document.title = cleanTitle;
            
            toast('Membuka pratinjau cetak PDF...', 'info');
            
            setTimeout(function() {
                window.print();
                setTimeout(function() {
                    document.title = prevTitle;
                    document.body.removeAttribute('data-print-part');
                }, 1000);
            }, 300);
        };
    }

    window.HSE = {
        toast: toast,
        downloadPdf: downloadPdf
    };
})();