'use client';

import { useEffect, useState, useCallback, type ChangeEvent } from 'react';
import { AdminTopbar } from '@/components/admin/AdminTopbar';
import { cn } from '@/lib/utils/cn';

interface MediaItem {
  id: string;
  filename: string;
  url: string;
  altText: string | null;
  mimeType: string;
  size: number;
  createdAt: string;
}

export default function MediaLibraryPage() {
  const [items, setItems] = useState<MediaItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [uploading, setUploading] = useState(false);
  const [selectedItem, setSelectedItem] = useState<MediaItem | null>(null);
  const [dragActive, setDragActive] = useState(false);

  useEffect(() => {
    fetchMedia();
  }, []);

  async function fetchMedia() {
    try {
      const res = await fetch('/api/admin/upload');
      if (res.ok) {
        const data = await res.json();
        setItems(Array.isArray(data) ? data : data.items || []);
      }
    } catch {
      // API not available
    } finally {
      setLoading(false);
    }
  }

  async function handleUpload(files: FileList | null) {
    if (!files || files.length === 0) return;

    setUploading(true);

    for (let i = 0; i < files.length; i++) {
      const formData = new FormData();
      formData.append('file', files[i]);

      try {
        await fetch('/api/admin/upload', {
          method: 'POST',
          body: formData,
        });
      } catch {
        // Upload failed for this file
      }
    }

    setUploading(false);
    fetchMedia();
  }

  function handleFileInput(e: ChangeEvent<HTMLInputElement>) {
    handleUpload(e.target.files);
    e.target.value = '';
  }

  const handleDrag = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    if (e.type === 'dragenter' || e.type === 'dragover') {
      setDragActive(true);
    } else if (e.type === 'dragleave') {
      setDragActive(false);
    }
  }, []);

  const handleDrop = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setDragActive(false);
    handleUpload(e.dataTransfer.files);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  async function handleDelete(item: MediaItem) {
    if (!confirm(`Delete "${item.filename}"? This cannot be undone.`)) return;

    try {
      await fetch(`/api/admin/upload?id=${item.id}`, { method: 'DELETE' });
      setSelectedItem(null);
      fetchMedia();
    } catch {
      // Handle error
    }
  }

  function formatSize(bytes: number) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
  }

  function formatDate(dateStr: string) {
    return new Date(dateStr).toLocaleDateString('en-IN', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    });
  }

  return (
    <>
      <AdminTopbar title="Media Library" />

      <div className="p-2xl">
        <div className="mb-xl flex items-center justify-between">
          <h2 className="text-h3 text-gray-900">Media Library</h2>
          <label className="inline-flex cursor-pointer items-center gap-sm rounded-sm bg-brand-primary px-lg py-sm text-sm font-medium text-white transition-colors hover:bg-brand-primary-dark">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
              <polyline points="17 8 12 3 7 8" />
              <line x1="12" x2="12" y1="3" y2="15" />
            </svg>
            Upload Files
            <input
              type="file"
              accept="image/*"
              multiple
              onChange={handleFileInput}
              className="hidden"
            />
          </label>
        </div>

        {/* Drop zone */}
        <div
          onDragEnter={handleDrag}
          onDragLeave={handleDrag}
          onDragOver={handleDrag}
          onDrop={handleDrop}
          className={cn(
            'mb-xl flex min-h-[120px] items-center justify-center rounded-md border-2 border-dashed transition-colors',
            dragActive
              ? 'border-brand-primary bg-brand-primary-bg'
              : 'border-gray-300 bg-gray-50',
            uploading && 'pointer-events-none opacity-60'
          )}
        >
          <div className="flex flex-col items-center gap-sm py-xl">
            {uploading ? (
              <>
                <div className="h-[32px] w-[32px] animate-spin rounded-full border-[3px] border-gray-200 border-t-brand-primary" />
                <p className="text-sm text-gray-500">Uploading...</p>
              </>
            ) : (
              <>
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" className="text-gray-400">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                  <polyline points="17 8 12 3 7 8" />
                  <line x1="12" x2="12" y1="3" y2="15" />
                </svg>
                <p className="text-sm text-gray-500">Drag and drop files here, or click Upload</p>
              </>
            )}
          </div>
        </div>

        <div className="grid grid-cols-1 gap-xl lg:grid-cols-3">
          {/* Media grid */}
          <div className={selectedItem ? 'lg:col-span-2' : 'lg:col-span-3'}>
            {loading ? (
              <div className="grid grid-cols-2 gap-md sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                {Array.from({ length: 10 }).map((_, i) => (
                  <div key={i} className="aspect-square animate-pulse rounded-sm bg-gray-200" />
                ))}
              </div>
            ) : items.length === 0 ? (
              <div className="flex flex-col items-center justify-center rounded-md border border-gray-200 bg-white py-4xl">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1" className="text-gray-300">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                  <circle cx="8.5" cy="8.5" r="1.5" />
                  <polyline points="21 15 16 10 5 21" />
                </svg>
                <p className="mt-lg text-sm text-gray-500">No media uploaded yet.</p>
              </div>
            ) : (
              <div className="grid grid-cols-2 gap-md sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                {items.map((item) => (
                  <button
                    key={item.id}
                    type="button"
                    onClick={() => setSelectedItem(item)}
                    className={cn(
                      'group relative aspect-square overflow-hidden rounded-sm border-2 transition-all',
                      selectedItem?.id === item.id
                        ? 'border-brand-primary ring-2 ring-brand-primary/30'
                        : 'border-gray-200 hover:border-gray-400'
                    )}
                  >
                    <img
                      src={item.url}
                      alt={item.altText || item.filename}
                      className="h-full w-full object-cover"
                    />
                    <div className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/60 to-transparent px-sm py-xs opacity-0 transition-opacity group-hover:opacity-100">
                      <p className="truncate text-caption text-white">{item.filename}</p>
                    </div>
                  </button>
                ))}
              </div>
            )}
          </div>

          {/* Details panel */}
          {selectedItem && (
            <div>
              <div className="sticky top-[80px] rounded-md border border-gray-200 bg-white shadow-xs">
                <div className="flex items-center justify-between border-b border-gray-200 px-xl py-lg">
                  <h3 className="text-h4 text-gray-900">Details</h3>
                  <button
                    type="button"
                    onClick={() => setSelectedItem(null)}
                    className="flex h-[28px] w-[28px] items-center justify-center rounded-sm text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600"
                    aria-label="Close"
                  >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                      <path d="M18 6 6 18" /><path d="m6 6 12 12" />
                    </svg>
                  </button>
                </div>

                <div className="p-xl">
                  {/* Preview */}
                  <img
                    src={selectedItem.url}
                    alt={selectedItem.altText || selectedItem.filename}
                    className="mb-xl w-full rounded-sm border border-gray-200 object-contain"
                  />

                  {/* File info */}
                  <div className="flex flex-col gap-lg">
                    <div>
                      <p className="text-caption font-medium uppercase text-gray-400">Filename</p>
                      <p className="mt-xs text-sm text-gray-900">{selectedItem.filename}</p>
                    </div>
                    <div>
                      <p className="text-caption font-medium uppercase text-gray-400">Size</p>
                      <p className="mt-xs text-sm text-gray-900">{formatSize(selectedItem.size)}</p>
                    </div>
                    <div>
                      <p className="text-caption font-medium uppercase text-gray-400">Uploaded</p>
                      <p className="mt-xs text-sm text-gray-900">{formatDate(selectedItem.createdAt)}</p>
                    </div>
                    <div>
                      <p className="mb-sm text-caption font-medium uppercase text-gray-400">URL</p>
                      <input
                        type="text"
                        value={selectedItem.url}
                        readOnly
                        onClick={(e) => (e.target as HTMLInputElement).select()}
                        className="h-[36px] w-full rounded-sm border border-gray-300 bg-gray-50 px-md text-caption text-gray-600 focus:border-brand-primary focus:outline-none"
                      />
                    </div>
                  </div>

                  {/* Actions */}
                  <div className="mt-xl border-t border-gray-200 pt-xl">
                    <button
                      type="button"
                      onClick={() => handleDelete(selectedItem)}
                      className="w-full rounded-sm border border-red-200 bg-red-50 px-lg py-sm text-sm font-medium text-danger transition-colors hover:bg-red-100"
                    >
                      Delete Permanently
                    </button>
                  </div>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </>
  );
}
