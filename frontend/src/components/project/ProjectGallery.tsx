'use client';

import { useState } from 'react';
import { cn } from '@/lib/utils/cn';
import { Lightbox } from '@/components/widgets/Lightbox';

interface ProjectGalleryProps {
  images: string[];
}

export function ProjectGallery({ images }: ProjectGalleryProps) {
  const [selectedIndex, setSelectedIndex] = useState(0);
  const [lightboxOpen, setLightboxOpen] = useState(false);

  if (!images || images.length === 0) {
    return (
      <div className="flex aspect-video w-full items-center justify-center rounded-md bg-gray-200">
        <svg
          width="48"
          height="48"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeWidth="1.5"
          strokeLinecap="round"
          strokeLinejoin="round"
          className="text-gray-400"
          aria-hidden="true"
        >
          <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z" />
          <circle cx="12" cy="13" r="3" />
        </svg>
      </div>
    );
  }

  const thumbnails = images.slice(0, 4);

  return (
    <div className="flex flex-col gap-sm">
      {/* Main image */}
      <button
        type="button"
        className="relative w-full overflow-hidden rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary focus-visible:ring-offset-2"
        onClick={() => setLightboxOpen(true)}
        aria-label="Open image gallery"
      >
        <div className="aspect-video w-full bg-gray-200">
          <img
            src={images[selectedIndex]}
            alt={`Project image ${selectedIndex + 1}`}
            className="h-full w-full object-cover"
          />
        </div>
        {images.length > 1 && (
          <div className="absolute bottom-md right-md rounded-full bg-black/60 px-md py-xs text-caption font-medium text-white tabular-nums">
            {selectedIndex + 1} / {images.length}
          </div>
        )}
      </button>

      {/* Thumbnail strip */}
      {images.length > 1 && (
        <div className="flex gap-sm">
          {thumbnails.map((src, index) => (
            <button
              key={index}
              type="button"
              className={cn(
                'relative h-[72px] w-1/4 overflow-hidden rounded-sm transition-all',
                'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary',
                index === selectedIndex
                  ? 'ring-2 ring-brand-primary'
                  : 'opacity-70 hover:opacity-100'
              )}
              onClick={() => setSelectedIndex(index)}
              aria-label={`View image ${index + 1}`}
            >
              <img
                src={src}
                alt={`Thumbnail ${index + 1}`}
                className="h-full w-full object-cover"
                loading="lazy"
              />
              {index === 3 && images.length > 4 && (
                <div className="absolute inset-0 flex items-center justify-center bg-black/50">
                  <span className="text-base font-semibold text-white">
                    +{images.length - 4}
                  </span>
                </div>
              )}
            </button>
          ))}
        </div>
      )}

      {/* Lightbox */}
      {lightboxOpen && (
        <Lightbox
          images={images}
          initialIndex={selectedIndex}
          onClose={() => setLightboxOpen(false)}
        />
      )}
    </div>
  );
}
