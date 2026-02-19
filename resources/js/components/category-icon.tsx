import {
    Building,
    Droplets,
    GraduationCap,
    HeartPulse,
    Trees,
    ShieldAlert,
    Bus,
    Scale,
    Briefcase,
    ShieldCheck,
    Wheat,
    MonitorSmartphone,
    Home,
    Zap,
    Accessibility,
    type LucideIcon,
} from 'lucide-react';

const iconMap: Record<string, LucideIcon> = {
    'building': Building,
    'droplets': Droplets,
    'graduation-cap': GraduationCap,
    'heart-pulse': HeartPulse,
    'trees': Trees,
    'shield-alert': ShieldAlert,
    'bus': Bus,
    'scale': Scale,
    'briefcase': Briefcase,
    'shield-check': ShieldCheck,
    'wheat': Wheat,
    'monitor-smartphone': MonitorSmartphone,
    'home': Home,
    'zap': Zap,
    'accessibility': Accessibility,
};

interface CategoryIconProps {
    name: string;
    className?: string;
}

export default function CategoryIcon({ name, className = 'h-4 w-4' }: CategoryIconProps) {
    const Icon = iconMap[name];

    if (!Icon) {
        return <span className="text-xs text-muted-foreground">{name}</span>;
    }

    return <Icon className={className} />;
}
