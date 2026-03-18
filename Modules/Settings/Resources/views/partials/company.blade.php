<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Informations de l'entreprise</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Nom de l'entreprise</label>
                <input type="text" name="settings[company_name]" class="form-control"
                       value="{{ $values['company_name'] ?? '' }}">
                <input type="hidden" name="types[company_name]" value="string">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Raison sociale</label>
                <input type="text" name="settings[legal_name]" class="form-control"
                       value="{{ $values['legal_name'] ?? '' }}">
                <input type="hidden" name="types[legal_name]" value="string">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">NIF (Numero d'identification fiscale)</label>
                <input type="text" name="settings[tax_number_nif]" class="form-control"
                       value="{{ $values['tax_number_nif'] ?? '' }}">
                <input type="hidden" name="types[tax_number_nif]" value="string">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">RCCM</label>
                <input type="text" name="settings[tax_number_rccm]" class="form-control"
                       value="{{ $values['tax_number_rccm'] ?? '' }}">
                <input type="hidden" name="types[tax_number_rccm]" value="string">
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Coordonnees</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="form-label">Adresse</label>
            <input type="text" name="settings[address]" class="form-control"
                   value="{{ $values['address'] ?? '' }}">
            <input type="hidden" name="types[address]" value="string">
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Ville</label>
                <input type="text" name="settings[city]" class="form-control"
                       value="{{ $values['city'] ?? '' }}">
                <input type="hidden" name="types[city]" value="string">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Pays</label>
                <input type="text" name="settings[country]" class="form-control"
                       value="{{ $values['country'] ?? '' }}">
                <input type="hidden" name="types[country]" value="string">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Telephone</label>
                <input type="text" name="settings[phone]" class="form-control"
                       value="{{ $values['phone'] ?? '' }}">
                <input type="hidden" name="types[phone]" value="string">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="settings[email]" class="form-control"
                       value="{{ $values['email'] ?? '' }}">
                <input type="hidden" name="types[email]" value="string">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Site web</label>
                <input type="url" name="settings[website]" class="form-control"
                       value="{{ $values['website'] ?? '' }}" placeholder="https://">
                <input type="hidden" name="types[website]" value="string">
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title mb-0">Parametres regionaux et comptables</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Devise</label>
                <select name="settings[currency]" class="form-select">
                    @php
                        $currencies = [
                            'XAF' => 'XAF - Franc CFA (CEMAC)',
                            'XOF' => 'XOF - Franc CFA (UEMOA)',
                            'USD' => 'USD - Dollar americain',
                            'EUR' => 'EUR - Euro',
                            'GBP' => 'GBP - Livre sterling',
                            'CDF' => 'CDF - Franc congolais',
                            'NGN' => 'NGN - Naira nigerien',
                            'GHS' => 'GHS - Cedi ghaneen',
                            'KES' => 'KES - Shilling kenyan',
                            'ZAR' => 'ZAR - Rand sud-africain',
                            'MAD' => 'MAD - Dirham marocain',
                            'TND' => 'TND - Dinar tunisien',
                        ];
                        $selected = $values['currency'] ?? 'XAF';
                    @endphp
                    @foreach($currencies as $code => $label)
                        <option value="{{ $code }}" @selected($selected === $code)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="types[currency]" value="string">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Debut de l'annee fiscale (mois)</label>
                <select name="settings[fiscal_year_start]" class="form-select">
                    @php
                        $months = [
                            1 => 'Janvier', 2 => 'Fevrier', 3 => 'Mars', 4 => 'Avril',
                            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Aout',
                            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Decembre',
                        ];
                        $selectedMonth = (int) ($values['fiscal_year_start'] ?? 1);
                    @endphp
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" @selected($selectedMonth === $num)>{{ $name }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="types[fiscal_year_start]" value="integer">
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Format de date</label>
                <select name="settings[date_format]" class="form-select">
                    @php
                        $formats = ['d/m/Y' => 'JJ/MM/AAAA', 'Y-m-d' => 'AAAA-MM-JJ', 'm/d/Y' => 'MM/JJ/AAAA', 'd-m-Y' => 'JJ-MM-AAAA'];
                        $selectedFormat = $values['date_format'] ?? 'd/m/Y';
                    @endphp
                    @foreach($formats as $fmt => $label)
                        <option value="{{ $fmt }}" @selected($selectedFormat === $fmt)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="types[date_format]" value="string">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Format d'heure</label>
                <select name="settings[time_format]" class="form-select">
                    @php
                        $timeFormats = ['H:i' => '24h (14:30)', 'h:i A' => '12h (02:30 PM)'];
                        $selectedTime = $values['time_format'] ?? 'H:i';
                    @endphp
                    @foreach($timeFormats as $fmt => $label)
                        <option value="{{ $fmt }}" @selected($selectedTime === $fmt)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="types[time_format]" value="string">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Fuseau horaire</label>
                <select name="settings[timezone]" class="form-select">
                    @php
                        $timezones = [
                            'Africa/Douala' => 'Afrique/Douala (WAT)',
                            'Africa/Kinshasa' => 'Afrique/Kinshasa (WAT)',
                            'Africa/Lagos' => 'Afrique/Lagos (WAT)',
                            'Africa/Brazzaville' => 'Afrique/Brazzaville (WAT)',
                            'Africa/Libreville' => 'Afrique/Libreville (WAT)',
                            'Africa/Lubumbashi' => 'Afrique/Lubumbashi (CAT)',
                            'Africa/Nairobi' => 'Afrique/Nairobi (EAT)',
                            'Africa/Casablanca' => 'Afrique/Casablanca (WET)',
                            'Europe/Paris' => 'Europe/Paris (CET)',
                            'UTC' => 'UTC',
                        ];
                        $selectedTz = $values['timezone'] ?? 'Africa/Douala';
                    @endphp
                    @foreach($timezones as $tz => $label)
                        <option value="{{ $tz }}" @selected($selectedTz === $tz)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="types[timezone]" value="string">
            </div>
        </div>
    </div>
</div>
