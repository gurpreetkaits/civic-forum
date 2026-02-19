import { useState } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import {
    Dialog,
    DialogContent,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { PostImage } from '@/types';
import { cn } from '@/lib/utils';

interface ImageGalleryProps {
    images: PostImage[];
}

export default function ImageGallery({ images }: ImageGalleryProps) {
    const [selectedIndex, setSelectedIndex] = useState<number | null>(null);

    if (!images || images.length === 0) {
        return null;
    }

    const sortedImages = [...images].sort(
        (a, b) => a.sort_order - b.sort_order,
    );

    const openImage = (index: number) => {
        setSelectedIndex(index);
    };

    const closeDialog = () => {
        setSelectedIndex(null);
    };

    const goToPrevious = () => {
        if (selectedIndex === null) return;
        setSelectedIndex(
            selectedIndex === 0 ? sortedImages.length - 1 : selectedIndex - 1,
        );
    };

    const goToNext = () => {
        if (selectedIndex === null) return;
        setSelectedIndex(
            selectedIndex === sortedImages.length - 1 ? 0 : selectedIndex + 1,
        );
    };

    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === 'ArrowLeft') {
            goToPrevious();
        } else if (e.key === 'ArrowRight') {
            goToNext();
        }
    };

    const gridCols =
        sortedImages.length === 1
            ? 'grid-cols-1'
            : sortedImages.length === 2
              ? 'grid-cols-2'
              : 'grid-cols-2 sm:grid-cols-3';

    return (
        <>
            {/* Thumbnail grid */}
            <div className={cn('grid gap-2', gridCols)}>
                {sortedImages.map((image, index) => (
                    <button
                        key={image.id}
                        className="group relative aspect-video overflow-hidden rounded-lg border bg-muted focus:outline-none focus:ring-2 focus:ring-ring"
                        onClick={() => openImage(index)}
                    >
                        <img
                            src={`/storage/${image.thumbnail_path ?? image.image_path}`}
                            alt={`Image ${index + 1}`}
                            className="h-full w-full object-cover transition-transform group-hover:scale-105"
                        />
                    </button>
                ))}
            </div>

            {/* Lightbox dialog */}
            <Dialog
                open={selectedIndex !== null}
                onOpenChange={(open) => {
                    if (!open) closeDialog();
                }}
            >
                <DialogContent
                    className="max-w-4xl p-0 overflow-hidden bg-black/95 border-none"
                    onKeyDown={handleKeyDown}
                >
                    <DialogTitle className="sr-only">
                        Image {selectedIndex !== null ? selectedIndex + 1 : ''} of{' '}
                        {sortedImages.length}
                    </DialogTitle>

                    {selectedIndex !== null && (
                        <div className="relative flex items-center justify-center">
                            <img
                                src={`/storage/${sortedImages[selectedIndex].image_path}`}
                                alt={`Image ${selectedIndex + 1}`}
                                className="max-h-[80vh] w-auto object-contain"
                            />

                            {/* Navigation arrows */}
                            {sortedImages.length > 1 && (
                                <>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="absolute left-2 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-black/50 text-white hover:bg-black/70 hover:text-white"
                                        onClick={goToPrevious}
                                    >
                                        <ChevronLeft className="h-6 w-6" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        className="absolute right-2 top-1/2 -translate-y-1/2 h-10 w-10 rounded-full bg-black/50 text-white hover:bg-black/70 hover:text-white"
                                        onClick={goToNext}
                                    >
                                        <ChevronRight className="h-6 w-6" />
                                    </Button>
                                </>
                            )}

                            {/* Image counter */}
                            <div className="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-black/60 px-3 py-1 text-xs text-white">
                                {selectedIndex + 1} / {sortedImages.length}
                            </div>
                        </div>
                    )}
                </DialogContent>
            </Dialog>
        </>
    );
}
