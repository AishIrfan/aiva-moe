@extends('layouts.shell')
@section('title', 'Documents')
@section('subtitle', 'circulars, forms, policies')

@section('content')

<div class="flex flex-wrap items-end justify-between gap-4 mb-5">
    <div>
        <div class="text-[10px] uppercase tracking-[0.18em] text-zinc-400 font-semibold mb-2">IPG / Communication / Documents</div>
        <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-zinc-900">
            0 on file <span class="text-zinc-400">for distribution.</span>
        </h1>
    </div>
</div>

<x-card class="mb-3" title="Upload document" subtitle="circulars, permission slips, policies">
    <form method="POST" action="{{ route('ipg.documents.store') }}" enctype="multipart/form-data"
          class="grid grid-cols-2 md:grid-cols-12 gap-2 text-sm items-center">
        @csrf
        <input name="title" required placeholder="Title"
               class="md:col-span-3 bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300 placeholder:text-zinc-400"/>
        <input name="category" required value="general"
               class="md:col-span-2 bg-zinc-50 border border-zinc-200 rounded-md px-3 py-1.5 focus:outline-none focus:bg-white focus:border-zinc-300"/>
        <input type="file" name="file"
               class="md:col-span-4 text-xs text-zinc-700 file:mr-3 file:px-3 file:py-1 file:rounded-md file:border-0 file:bg-zinc-900 file:text-white file:text-xs file:font-medium hover:file:bg-zinc-800 file:cursor-pointer"/>
        <label class="md:col-span-2 flex items-center gap-1.5 text-xs">
            <input type="checkbox" name="requires_ack" value="1" class="rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500"/>
            Requires ack
        </label>
        <button class="md:col-span-1 inline-flex items-center justify-center bg-zinc-900 text-white rounded-md px-3 py-1.5 font-medium hover:bg-zinc-800 active:translate-y-[1px] transition">Upload</button>
    </form>
</x-card>

<div class="bg-white border border-zinc-200 rounded-xl p-10 text-center">
    <div class="text-sm font-medium text-zinc-900">No documents uploaded yet</div>
    <div class="text-xs text-zinc-500 mt-1">Upload circulars and forms here. Acknowledgment tracking lands in Phase 4.</div>
</div>

@endsection
