import { useRef, useState, useCallback } from 'react';
import { Upload, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { useTranslation } from 'react-i18next';

interface ImageUploadZoneProps {
    images: File[];
    onImagesChange: (images: File[]) => void;
}

const MAX_IMAGES = 5;
const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

export default function ImageUploadZone({
    images,
    onImagesChange,
}: ImageUploadZoneProps) {
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [isDragging, setIsDragging] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const { t } = useTranslation();

    const validateAndAddFiles = useCallback(
        (files: FileList | File[]) => {
            setError(null);
            const fileArray = Array.from(files);
            const validFiles: File[] = [];

            for (const file of fileArray) {
                if (!file.type.startsWith('image/')) {
                    setError(t('upload.onlyImages'));
                    continue;
                }
                if (file.size > MAX_FILE_SIZE) {
                    setError(t('upload.maxFileSize'));
                    continue;
                }
                validFiles.push(file);
            }

            const totalCount = images.length + validFiles.length;
            if (totalCount > MAX_IMAGES) {
                setError(t('upload.maxImagesError', { count: MAX_IMAGES }));
                const allowed = MAX_IMAGES - images.length;
                validFiles.splice(allowed);
            }

            if (validFiles.length > 0) {
                onImagesChange([...images, ...validFiles]);
            }
        },
        [images, onImagesChange, t],
    );

    const handleDragOver = (e: React.DragEvent) => {
        e.preventDefault();
        setIsDragging(true);
    };

    const handleDragLeave = (e: React.DragEvent) => {
        e.preventDefault();
        setIsDragging(false);
    };

    const handleDrop = (e: React.DragEvent) => {
        e.preventDefault();
        setIsDragging(false);
        if (e.dataTransfer.files.length > 0) {
            validateAndAddFiles(e.dataTransfer.files);
        }
    };

    const handleFileSelect = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files.length > 0) {
            validateAndAddFiles(e.target.files);
        }
        // Reset the input so the same file can be selected again
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    };

    const removeImage = (index: number) => {
        const updated = images.filter((_, i) => i !== index);
        onImagesChange(updated);
        setError(null);
    };

    return (
        <div className="space-y-3">
            {/* Drop zone */}
            <div
                className={cn(
                    'flex flex-col items-center justify-center rounded-lg border-2 border-dashed p-6 transition-colors cursor-pointer',
                    isDragging
                        ? 'border-primary bg-accent'
                        : 'border-muted-foreground/25 hover:border-muted-foreground/50',
                    images.length >= MAX_IMAGES && 'opacity-50 cursor-not-allowed',
                )}
                onDragOver={handleDragOver}
                onDragLeave={handleDragLeave}
                onDrop={handleDrop}
                onClick={() => {
                    if (images.length < MAX_IMAGES) {
                        fileInputRef.current?.click();
                    }
                }}
            >
                <Upload className="mb-2 h-8 w-8 text-muted-foreground" />
                <p className="text-sm font-medium text-muted-foreground">
                    {t('upload.dragDrop')}
                </p>
                <p className="mt-1 text-xs text-muted-foreground">
                    {t('upload.maxImages', { count: MAX_IMAGES })}
                </p>
            </div>

            <input
                ref={fileInputRef}
                type="file"
                accept="image/*"
                multiple
                className="hidden"
                onChange={handleFileSelect}
            />

            {/* Error message */}
            {error && (
                <p className="text-sm text-destructive">{error}</p>
            )}

            {/* Preview thumbnails */}
            {images.length > 0 && (
                <div className="flex flex-wrap gap-3">
                    {images.map((file, index) => (
                        <div
                            key={`${file.name}-${index}`}
                            className="group relative h-20 w-20 overflow-hidden rounded-lg border"
                        >
                            <img
                                src={URL.createObjectURL(file)}
                                alt={file.name}
                                className="h-full w-full object-cover"
                            />
                            <Button
                                variant="destructive"
                                size="icon"
                                className="absolute right-1 top-1 h-5 w-5 opacity-0 transition-opacity group-hover:opacity-100"
                                onClick={(e) => {
                                    e.stopPropagation();
                                    removeImage(index);
                                }}
                            >
                                <X className="h-3 w-3" />
                            </Button>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
