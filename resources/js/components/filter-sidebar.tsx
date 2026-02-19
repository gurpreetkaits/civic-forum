import { router, usePage } from '@inertiajs/react';
import { TrendingUp, Clock, ThumbsUp } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { PageProps } from '@/types';
import { cn } from '@/lib/utils';
import { useTranslation } from 'react-i18next';
import CategoryIcon from '@/components/category-icon';

interface FilterSidebarProps {
    currentFilters: {
        state_id?: string;
        category?: string;
        sort?: string;
    };
    baseUrl: string;
}

export default function FilterSidebar({
    currentFilters,
    baseUrl,
}: FilterSidebarProps) {
    const { states, categories } = usePage<PageProps>().props;
    const { t } = useTranslation();

    const sortOptions = [
        { value: 'trending', label: t('filter.trending'), icon: TrendingUp },
        { value: 'newest', label: t('filter.newest'), icon: Clock },
        { value: 'most-voted', label: t('filter.mostVoted'), icon: ThumbsUp },
    ];

    const applyFilter = (key: string, value: string | undefined) => {
        const params: Record<string, string> = {};

        if (currentFilters.state_id) params.state_id = currentFilters.state_id;
        if (currentFilters.sort) params.sort = currentFilters.sort;

        if (value) {
            params[key] = value;
        } else {
            delete params[key];
        }

        // If selecting a category, navigate to /categories/{slug} with other filters as query params
        if (key === 'category' && value) {
            const categoryParams: Record<string, string> = {};
            if (params.state_id) categoryParams.state_id = params.state_id;
            if (params.sort) categoryParams.sort = params.sort;

            router.get(`/categories/${value}`, categoryParams, {
                preserveState: true,
                preserveScroll: true,
            });
            return;
        }

        // If clearing category and we're on a category page, go back to home
        if (key === 'category' && !value && baseUrl !== '/') {
            const homeParams: Record<string, string> = {};
            if (params.state_id) homeParams.state_id = params.state_id;
            if (params.sort) homeParams.sort = params.sort;

            router.get('/', homeParams, {
                preserveState: true,
                preserveScroll: true,
            });
            return;
        }

        router.get(baseUrl, params, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    return (
        <div className="space-y-6">
            {/* Sort options */}
            <div>
                <h4 className="mb-2 text-sm font-semibold text-muted-foreground uppercase tracking-wide">
                    {t('filter.sortBy')}
                </h4>
                <div className="flex flex-col gap-1">
                    {sortOptions.map((option) => {
                        const Icon = option.icon;
                        const isActive =
                            currentFilters.sort === option.value ||
                            (!currentFilters.sort && option.value === 'trending');

                        return (
                            <Button
                                key={option.value}
                                variant="ghost"
                                size="sm"
                                className={cn(
                                    'justify-start gap-2',
                                    isActive &&
                                        'bg-accent text-accent-foreground',
                                )}
                                onClick={() => applyFilter('sort', option.value)}
                            >
                                <Icon className="h-4 w-4" />
                                {option.label}
                            </Button>
                        );
                    })}
                </div>
            </div>

            {/* State filter */}
            <div>
                <h4 className="mb-2 text-sm font-semibold text-muted-foreground uppercase tracking-wide">
                    {t('filter.stateUt')}
                </h4>
                <Select
                    value={currentFilters.state_id ?? 'all'}
                    onValueChange={(value) =>
                        applyFilter('state_id', value === 'all' ? undefined : value)
                    }
                >
                    <SelectTrigger className="w-full">
                        <SelectValue placeholder={t('filter.allStates')} />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">{t('filter.allStates')}</SelectItem>
                        {states?.map((state) => (
                            <SelectItem
                                key={state.id}
                                value={String(state.id)}
                            >
                                {state.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>

            {/* Category filter */}
            <div>
                <h4 className="mb-2 text-sm font-semibold text-muted-foreground uppercase tracking-wide">
                    {t('filter.categories')}
                </h4>
                <div className="flex flex-col gap-1">
                    <Button
                        variant="ghost"
                        size="sm"
                        className={cn(
                            'justify-start',
                            !currentFilters.category &&
                                'bg-accent text-accent-foreground',
                        )}
                        onClick={() => applyFilter('category', undefined)}
                    >
                        {t('filter.allCategories')}
                    </Button>
                    {categories?.map((category) => (
                        <Button
                            key={category.id}
                            variant="ghost"
                            size="sm"
                            className={cn(
                                'justify-start gap-2',
                                currentFilters.category === category.slug &&
                                    'bg-accent text-accent-foreground',
                            )}
                            onClick={() =>
                                applyFilter('category', category.slug)
                            }
                        >
                            <CategoryIcon name={category.icon} />
                            {category.translated_name}
                        </Button>
                    ))}
                </div>
            </div>
        </div>
    );
}
