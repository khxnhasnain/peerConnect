@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-700 bg-[#111c31] text-slate-100 placeholder-slate-400 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm']) }}>