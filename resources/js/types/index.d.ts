export interface User {
  id: number
  name: string
  email: string
  email_verified_at?: string;
  role: string
  created_at?: string
  updated_at?: string
}

export interface CartItem {
  product_id: number
  sku: string
  name: string
  price: number
  default_price: number
  quantity: number
  unit_conversion_id: number | null
  unit_conversions: {
    id: number
    unit_name: string
    conversion: number
    sell_price: number
  }[]
  unit_name: string
  default_unit_name: string
  conversion: number
  stock: number
}

export interface CategoryUnit {
  id: number
  name: string
}

export interface PaginationLink {
  url: string | null
  label: string
  page?: number | null
  active: boolean
}

export interface PaginationMeta {
  current_page: number
  from: number
  last_page: number
  per_page: number
  to: number
  total: number
  next_page_url: string | null
  prev_page_url: string | null
  links: PaginationLink[]
}

export interface Paginated<T> extends PaginationMeta {
  data: T[]
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
};
