{{-- Site Info → Size guide (plain text, one line per row; "|" separates columns), or a default chart. --}}
@php
    $rows = collect(preg_split('/\R/', trim((string) ($content ?? ''))))->map(fn ($line) => trim($line))->filter();
    $isTable = $rows->count() > 1 && $rows->every(fn ($line) => str_contains($line, '|'));
@endphp
@if ($rows->isEmpty())
    <p class="muted small">Measurements are body measurements in inches. If you're between sizes, size up for a relaxed fit.</p>
    <div class="table-scroll">
        <table class="size-table">
            <thead><tr><th>Size</th><th>Chest</th><th>Waist</th><th>Hip</th><th>Length</th></tr></thead>
            <tbody>
                <tr><td>XS</td><td>32–33</td><td>26–27</td><td>35–36</td><td>27</td></tr>
                <tr><td>S</td><td>34–35</td><td>28–29</td><td>37–38</td><td>27.5</td></tr>
                <tr><td>M</td><td>36–38</td><td>30–32</td><td>39–41</td><td>28</td></tr>
                <tr><td>L</td><td>39–41</td><td>33–35</td><td>42–44</td><td>28.5</td></tr>
                <tr><td>XL</td><td>42–44</td><td>36–38</td><td>45–47</td><td>29</td></tr>
                <tr><td>XXL</td><td>45–47</td><td>39–41</td><td>48–50</td><td>29.5</td></tr>
            </tbody>
        </table>
    </div>
@elseif ($isTable)
    <div class="table-scroll">
        <table class="size-table">
            <thead><tr>@foreach (explode('|', $rows->first()) as $cell)<th>{{ trim($cell) }}</th>@endforeach</tr></thead>
            <tbody>
                @foreach ($rows->slice(1) as $row)
                    <tr>@foreach (explode('|', $row) as $cell)<td>{{ trim($cell) }}</td>@endforeach</tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="rich-text">{!! nl2br(e($content)) !!}</div>
@endif
<h3 class="size-guide__how">How to measure</h3>
<ul class="size-guide__tips">
    <li><strong>Chest</strong> — around the fullest part, keeping the tape level.</li>
    <li><strong>Waist</strong> — around your natural waistline.</li>
    <li><strong>Hip</strong> — around the fullest part of your hips.</li>
</ul>
