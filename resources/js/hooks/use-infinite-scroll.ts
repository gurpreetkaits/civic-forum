import { useCallback, useEffect, useRef, useState } from 'react';
import { router } from '@inertiajs/react';
import { PaginatedData } from '@/types';

export function useInfiniteScroll<T extends { id: number }>(
    paginatedData: PaginatedData<T>,
    only: string = 'posts',
) {
    const [items, setItems] = useState<T[]>(paginatedData.data);
    const [nextPageUrl, setNextPageUrl] = useState<string | null>(
        paginatedData.next_page_url,
    );
    const [loading, setLoading] = useState(false);
    const loadingRef = useRef(false);
    const sentinelRef = useRef<HTMLDivElement>(null);
    const lastPageRef = useRef(paginatedData.current_page);

    // When paginatedData changes:
    // page === 1 → reset (filter changed or fresh navigation)
    // page > last tracked → append (infinite scroll loaded next page)
    useEffect(() => {
        if (paginatedData.current_page === 1) {
            setItems(paginatedData.data);
        } else if (paginatedData.current_page > lastPageRef.current) {
            setItems((prev) => [...prev, ...paginatedData.data]);
        }
        lastPageRef.current = paginatedData.current_page;
        setNextPageUrl(paginatedData.next_page_url);
        setLoading(false);
        loadingRef.current = false;
    }, [paginatedData]);

    const loadMore = useCallback(() => {
        if (!nextPageUrl || loadingRef.current) return;
        loadingRef.current = true;
        setLoading(true);

        router.get(nextPageUrl, {}, {
            preserveState: true,
            preserveScroll: true,
            only: [only],
        });
    }, [nextPageUrl, only]);

    useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                if (entries[0]?.isIntersecting && !loadingRef.current && nextPageUrl) {
                    loadMore();
                }
            },
            { rootMargin: '300px' },
        );

        const el = sentinelRef.current;
        if (el) observer.observe(el);

        return () => observer.disconnect();
    }, [loadMore, nextPageUrl]);

    return { items, sentinelRef, loading, hasMore: !!nextPageUrl };
}
