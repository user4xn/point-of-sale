import { PageProps as InertiaPageProps } from '@inertiajs/core'

// Definisikan tipe store sesuai middleware
interface Store {
  name: string
  address?: string
  contact?: string
  logo?: string | null
}

declare module '@inertiajs/core' {
  interface PageProps extends InertiaPageProps {
    auth: {
      user: {
        id: number
        name: string
        email: string
        role: string
      } | null
    }
    store: Store | null
    flash?: {
      success?: string
      error?: string
    }
  }
}
