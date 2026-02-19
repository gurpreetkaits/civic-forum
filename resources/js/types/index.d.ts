import { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    username: string;
    email: string;
    email_verified_at?: string;
    bio?: string;
    avatar_path?: string;
    state_id?: number;
    city_id?: number;
    reputation: number;
    state?: State;
    city?: City;
}

export interface State {
    id: number;
    name: string;
    code: string;
    type: 'state' | 'ut';
}

export interface City {
    id: number;
    name: string;
    state_id: number;
    state?: State;
}

export interface Category {
    id: number;
    name: string;
    name_hi?: string;
    slug: string;
    icon: string;
    description: string;
    description_hi?: string;
    sort_order: number;
    translated_name: string;
    translated_description: string;
}

export interface Post {
    id: number;
    user_id: number;
    category_id: number;
    state_id?: number;
    city_id?: number;
    title: string;
    slug: string;
    body: string;
    status: 'draft' | 'published' | 'archived';
    vote_count: number;
    comment_count: number;
    view_count: number;
    published_at?: string;
    created_at: string;
    updated_at: string;
    user?: User;
    category?: Category;
    state?: State;
    city?: City;
    images?: PostImage[];
    tags?: Tag[];
    user_vote?: number | null;
}

export interface PostImage {
    id: number;
    post_id: number;
    image_path: string;
    thumbnail_path?: string;
    sort_order: number;
}

export interface Comment {
    id: number;
    post_id: number;
    user_id: number;
    parent_id?: number;
    body: string;
    vote_count: number;
    depth: number;
    created_at: string;
    updated_at: string;
    user?: User;
    replies?: Comment[];
    user_vote?: number | null;
}

export interface Tag {
    id: number;
    name: string;
    slug: string;
}

export interface PaginatedData<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User | null;
    };
    categories: Category[];
    states: State[];
    locale: 'en' | 'hi';
    ziggy: Config & { location: string };
    flash?: {
        success?: string;
        error?: string;
    };
};
