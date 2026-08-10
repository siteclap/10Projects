'use client';

import { useState, useRef, useCallback } from 'react';
import { cn } from '@/lib/utils/cn';
import { Lightbox } from '@/components/widgets/Lightbox';

interface ProjectGalleryProps {
  images: string[];
  badge?: string;
}

export function ProjectGallery({ images, badge }: ProjectGalleryProps) {
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const [lightboxIndex, setLightboxIndex] = useState(0);

  // Mobile slider state
  const [currentSlide, setCurrentSlide] = useState(0);
  const touchStartX = useRef(0);
  const touchDeltaX = useRef(0);
  const isSwiping = useRef(false);

  const openLightbox = useCallback((index: number) => {
    setLightboxIndex(index);
    setLightboxOpen(true);
  }, []);

  const handleTouchStart = useCallback((e: React.TouchEvent) => {
    touchStartX.current = e.touches[0].clientX;
    touchDeltaX.current = 0;
    isSwiping.current = true;
  }, []);

  const handleTouchMove = useCallback((e: React.TouchEvent) => {
    if (!isSwiping.current) return;
    touchDeltaX.current = e.touches[0].clientX - touchStartX.current;
  }, []);

  const handleTouchEnd = useCallback(() => {
    if (!isSwiping.current) return;
    isSwiping.current = false;
    const threshold = 50;
    if (touchDeltaX.current < -threshold && currentSlide < images.length - 1) {
      setCurrentSlide((p) => p + 1);
    } else if (touchDeltaX.current > threshold && currentSlide > 0) {
      setCurrentSlide((p) => p - 1);
    }
  }, [currentSlide, images.length]);

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

  // Desktop: main (index 0) + up to 4 thumbnails (indices 1-4)
  const mainImage = images[0];
  const gridImages = images.slice(1, 5);
  const remainingCount = images.length - 5;

  return (
    <>
      {/* ── Mobile Slider ── */}
      <div
        className="relative overflow-hidden rounded-lg md:hidden"
        onTouchStart={handleTouchStart}
        onTouchMove={handleTouchMove}
        onTouchEnd={handleTouchEnd}
      >
        {/* Slides track */}
        <div
          className="flex transition-transform duration-300 ease-out"
          style={{ transform: `translateX(-${currentSlide * 100}%)` }}
        >
          {images.map((src, i) => (
            <div key={i} className="aspect-[4/3] w-full shrink-0 bg-gray-200">
              <img
                src={src}
                alt={`Project view ${i + 1}`}
                className="h-full w-full object-cover"
                loading={i === 0 ? 'eager' : 'lazy'}
                draggable={false}
              />
            </div>
          ))}
        </div>

        {/* Badge */}
        {badge && (
          <span className="absolute left-md top-md rounded-sm bg-accent px-md py-xs text-caption font-semibold text-white shadow-sm">
            {badge}
          </span>
        )}

        {/* Gallery button — opens lightbox */}
        <button
          type="button"
          onClick={() => openLightbox(currentSlide)}
          className="absolute bottom-md right-md flex h-[36px] w-[36px] items-center justify-center rounded-full bg-black/50 text-white backdrop-blur-sm"
          aria-label="Open gallery"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
            <circle cx="9" cy="9" r="2" />
            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
          </svg>
        </button>

        {/* Image counter */}
        <div className="absolute bottom-md left-md rounded-full bg-black/50 px-md py-xs text-caption font-medium tabular-nums text-white backdrop-blur-sm">
          {currentSlide + 1} / {images.length}
        </div>

        {/* Progress bar segments */}
        <div className="absolute bottom-0 left-0 right-0 flex gap-[2px] px-sm pb-xs">
          {images.map((_, i) => (
            <div
              key={i}
              className={cn(
                'h-[3px] flex-1 rounded-full transition-colors duration-300',
                i <= currentSlide ? 'bg-brand-primary' : 'bg-white/40'
              )}
            />
          ))}
        </div>
      </div>

      {/* ── Desktop Grid: 1 large (50%) + 2×2 thumbnails (50%) ── */}
      <div className="relative hidden gap-md md:grid md:grid-cols-4 md:grid-rows-2">
        {/* Badge — top-right corner of entire grid */}
        {badge && (
          <span className="absolute right-md top-md z-10 rounded-sm bg-accent px-lg py-xs text-sm font-semibold text-white shadow-md">
            {badge}
          </span>
        )}

        {/* Main image — col-span-2, row-span-2 (left half, full height) */}
        <button
          type="button"
          className="relative col-span-2 row-span-2 overflow-hidden rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
          onClick={() => openLightbox(0)}
          aria-label="View main image"
        >
          <div className="h-full w-full bg-gray-200">
            <img
              src={mainImage}
              alt="Project main view"
              className="h-full w-full object-cover"
            />
          </div>

          {/* Action buttons — vertical stack on main image */}
          <div className="absolute left-md top-md flex flex-col gap-sm">
            <span
              className="flex h-[38px] w-[38px] items-center justify-center rounded-full bg-black/50 text-white backdrop-blur-sm transition-colors hover:bg-black/70"
              aria-label="Share"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <circle cx="18" cy="5" r="3" />
                <circle cx="6" cy="12" r="3" />
                <circle cx="18" cy="19" r="3" />
                <path d="m8.59 13.51 6.83 3.98" />
                <path d="m15.41 6.51-6.82 3.98" />
              </svg>
            </span>
            <span
              className="flex h-[38px] w-[38px] items-center justify-center rounded-full bg-black/50 text-white backdrop-blur-sm transition-colors hover:bg-black/70"
              aria-label="Compare"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <rect width="18" height="18" x="3" y="3" rx="2" />
                <path d="M12 3v18" />
              </svg>
            </span>
            <span
              className="flex h-[38px] w-[38px] items-center justify-center rounded-full bg-black/50 text-white backdrop-blur-sm transition-colors hover:bg-black/70"
              aria-label="Save"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" />
              </svg>
            </span>
          </div>
        </button>

        {/* 4 thumbnails — each takes 1 col, 1 row (right half, 2×2) */}
        {gridImages.map((src, i) => {
          const imageIndex = i + 1;
          const isLast = i === gridImages.length - 1;
          const showViewAll = isLast && remainingCount > 0;

          return (
            <button
              key={imageIndex}
              type="button"
              className="relative overflow-hidden rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-primary"
              onClick={() => openLightbox(imageIndex)}
              aria-label={
                showViewAll
                  ? `View all ${images.length} images`
                  : `View image ${imageIndex + 1}`
              }
            >
              <div className="aspect-[4/3] w-full bg-gray-200">
                <img
                  src={src}
                  alt={`Project view ${imageIndex + 1}`}
                  className="h-full w-full object-cover"
                  loading="lazy"
                />
              </div>

              {showViewAll && (
                <div className="absolute inset-0 flex items-center justify-center bg-black/50 transition-colors hover:bg-black/60">
                  <span className="text-base font-semibold text-white">
                    VIEW ALL
                  </span>
                </div>
              )}
            </button>
          );
        })}

        {/* Fill empty cells if fewer than 4 thumbnails */}
        {gridImages.length > 0 && gridImages.length < 4 &&
          Array.from({ length: 4 - gridImages.length }).map((_, i) => (
            <div key={`empty-${i}`} className="rounded-lg bg-gray-100" />
          ))
        }
      </div>

      {/* Lightbox */}
      {lightboxOpen && (
        <Lightbox
          images={images}
          initialIndex={lightboxIndex}
          onClose={() => setLightboxOpen(false)}
        />
      )}
    </>
  );
}
