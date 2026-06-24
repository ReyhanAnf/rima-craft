@props([
    'name',
    'label' => 'Pilih atau Tarik File',
    'accept' => 'image/*'
])

<div x-data="{
    dragover: false,
    files: [],
    
    handleFiles(event) {
        const newFiles = Array.from(event.target.files);
        this.addFiles(newFiles);
    },
    
    handleDrop(event) {
        this.dragover = false;
        const newFiles = Array.from(event.dataTransfer.files);
        this.addFiles(newFiles);
    },
    
    addFiles(newFiles) {
        newFiles.forEach(f => {
            if (f.type.startsWith('image/')) {
                f.previewUrl = URL.createObjectURL(f);
                this.files.push(f);
            }
        });
        this.syncInput();
    },
    
    removeFile(index) {
        URL.revokeObjectURL(this.files[index].previewUrl);
        this.files.splice(index, 1);
        this.syncInput();
    },
    
    syncInput() {
        const dt = new DataTransfer();
        this.files.forEach(f => dt.items.add(f));
        this.$refs.fileInput.files = dt.files;
    }
}" class="w-full">
    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ $label }}</label>
    
    <div 
        @dragover.prevent="dragover = true"
        @dragleave.prevent="dragover = false"
        @drop.prevent="handleDrop($event)"
        :class="{'border-primary-500 bg-primary-50 dark:bg-primary-500/10': dragover, 'border-gray-300 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900': !dragover}"
        class="relative flex flex-col items-center justify-center w-full p-4 border-2 border-dashed rounded-md transition cursor-pointer group hover:bg-gray-100 dark:hover:bg-gray-800"
    >
        <svg class="w-8 h-8 text-gray-400 group-hover:text-primary-500 transition mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
        <p class="text-xs text-gray-500 dark:text-gray-400 text-center"><span class="font-bold text-primary-600 dark:text-primary-400">Klik untuk browse</span> atau tarik dan lepas file kesini</p>
        <input x-ref="fileInput" type="file" name="{{ $name }}" multiple accept="{{ $accept }}" @change="handleFiles($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
    </div>

    <!-- Previews -->
    <div x-show="files.length > 0" class="mt-3" style="display: none;">
        <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-2">Preview yang akan diunggah</label>
        <div class="grid grid-cols-4 sm:grid-cols-5 gap-2">
            <template x-for="(file, index) in files" :key="index">
                <div class="relative group aspect-square rounded-md overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm bg-gray-100 dark:bg-gray-800">
                    <img :src="file.previewUrl" class="w-full h-full object-cover">
                    <button type="button" @click.prevent="removeFile(index)" class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded opacity-0 group-hover:opacity-100 transition shadow hover:bg-red-600 z-10">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>

