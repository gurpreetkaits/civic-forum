import { useState, useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { PageProps, City } from '@/types';
import { useTranslation } from 'react-i18next';

interface LocationPickerProps {
    stateId: number | null;
    cityId: number | null;
    onStateChange: (stateId: number | null) => void;
    onCityChange: (cityId: number | null) => void;
}

export default function LocationPicker({
    stateId,
    cityId,
    onStateChange,
    onCityChange,
}: LocationPickerProps) {
    const { states } = usePage<PageProps>().props;
    const [cities, setCities] = useState<City[]>([]);
    const [loadingCities, setLoadingCities] = useState(false);
    const { t } = useTranslation();

    useEffect(() => {
        if (!stateId) {
            setCities([]);
            return;
        }

        setLoadingCities(true);
        fetch(`/api/states/${stateId}/cities`)
            .then((res) => res.json())
            .then((data: City[]) => {
                setCities(data);
            })
            .catch(() => {
                setCities([]);
            })
            .finally(() => {
                setLoadingCities(false);
            });
    }, [stateId]);

    const handleStateChange = (value: string) => {
        if (value === 'none') {
            onStateChange(null);
            onCityChange(null);
        } else {
            const newStateId = Number(value);
            onStateChange(newStateId);
            onCityChange(null);
        }
    };

    const handleCityChange = (value: string) => {
        if (value === 'none') {
            onCityChange(null);
        } else {
            onCityChange(Number(value));
        }
    };

    return (
        <div className="flex flex-col gap-3 sm:flex-row sm:gap-4">
            <div className="flex-1">
                <label className="mb-1.5 block text-sm font-medium">
                    {t('location.stateUt')}
                </label>
                <Select
                    value={stateId ? String(stateId) : 'none'}
                    onValueChange={handleStateChange}
                >
                    <SelectTrigger className="w-full">
                        <SelectValue placeholder={t('location.selectState')} />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="none">{t('location.selectState')}</SelectItem>
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

            <div className="flex-1">
                <label className="mb-1.5 block text-sm font-medium">
                    {t('location.city')}
                </label>
                <Select
                    value={cityId ? String(cityId) : 'none'}
                    onValueChange={handleCityChange}
                    disabled={!stateId || loadingCities}
                >
                    <SelectTrigger className="w-full">
                        <SelectValue
                            placeholder={
                                loadingCities
                                    ? t('location.loadingCities')
                                    : t('location.selectCity')
                            }
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="none">{t('location.selectCity')}</SelectItem>
                        {cities.map((city) => (
                            <SelectItem
                                key={city.id}
                                value={String(city.id)}
                            >
                                {city.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>
        </div>
    );
}
