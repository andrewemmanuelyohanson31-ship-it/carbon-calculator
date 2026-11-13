@props(['name', 'label', 'unit' => null, 'step' => 'any', 'min' => 0, 'max' => null, 'value' => null, 'hint' => null])

<div class="field">
    <label for="{{ $name }}">{{ $label }}</label>
    <div class="input-unit">
        <input type="number" id="{{ $name }}" name="{{ $name }}" inputmode="decimal"
               step="{{ $step }}" min="{{ $min }}" @if ($max) max="{{ $max }}" @endif
               value="{{ old($name, $value) }}" @error($name) aria-invalid="true" @enderror>
        @if ($unit) <span>{{ $unit }}</span> @endif
    </div>
    @if ($hint) <small>{{ $hint }}</small> @endif
    @error($name) <p class="error">{{ $message }}</p> @enderror
</div>
