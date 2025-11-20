import { computed, ref } from 'vue'

const STORAGE_KEY = 'app_locale'
const SUPPORTED = ['uk', 'en']

const en = {
  nav: {
    home: 'Home',
    devices: 'Devices',
    setup: 'Setup',
    product: 'Product',
  },
  badge: {
    demo: 'Demo mode',
    status: 'online',
  },
  actions: {
    login: 'Log in',
    register: 'Register',
    logout: 'Log out',
    openDemo: 'Open demo',
    triggerDemo: 'Demo',
    addDevice: 'Add device',
    setup: 'Setup',
    monitoring: 'Monitoring',
    refresh: 'Refresh',
    autoRefresh: 'Auto refresh every 10 s',
    close: 'Close',
    bookCall: 'Book a call',
    start: 'Get started',
    goToDevices: 'Go to devices',
  },
  home: {
    hero: {
      subtitle: 'Reinvent energy monitoring & IoT operations',
      lead: 'Onboard hardware securely, stream power metrics in real time and automate responses with CONNECT.',
      primaryCta: 'Log in',
      secondaryCta: 'Register',
      demoCta: 'Open demo',
    },
    featuresTitle: 'What CONNECT delivers',
    features: [
      {
        title: 'Device on-boarding',
        text: 'Secure registration of Shelly and other IoT plugs in under a minute.',
      },
      {
        title: 'Real-time analytics',
        text: 'Instant telemetry, hour-by-hour averages and peak notifications.',
      },
      {
        title: 'Edge automation',
        text: 'Local rules and OTA updates that keep working when the internet drops.',
      },
    ],
    learnMore: 'Learn more',
    whyTitle: 'Why teams choose CONNECT',
    why: [
      { title: '99.9% uptime', text: 'Automatic fail-over keeps dashboards online.' },
      { title: '< 5 s latency', text: 'A global edge network minimizes lag.' },
      { title: 'GDPR compliant', text: 'EU hosting, AES-256, right-to-erasure.' },
      { title: '24/7 support', text: 'Senior engineers on duty with <15 min SLA.' },
    ],
    tabsTitle: 'Everything you need',
    tabs: [
      {
        id: 'security',
        label: 'Security',
        title: 'Security by design',
        text: 'Mutual TLS, rotating credentials, role-based access and full audits.',
        bullets: [
          'SSO (OIDC/SAML), 2FA, IP allow-lists',
          'Fine-grained API tokens',
          'Full audit log & immutable backups',
        ],
      },
      {
        id: 'analytics',
        label: 'Analytics',
        title: 'Analytics & insights',
        text: 'Timeseries charts, anomaly alerts and drag-and-drop dashboards.',
        bullets: [
          'Ready-made device reports',
          'Anomaly detection & alerts',
          'CSV/Parquet export, webhooks',
        ],
      },
      {
        id: 'automation',
        label: 'Automation',
        title: 'Edge automation',
        text: 'If/else rules, thresholds and OTA updates even without internet.',
        bullets: [
          'Visual rule engine',
          'Delays, counters, complex flows',
          'OTA firmware distribution',
        ],
      },
    ],
    usecasesTitle: 'Use cases',
    usecases: [
      { title: 'Smart office', text: 'Lighting, HVAC and socket telemetry with peak alerts.' },
      { title: 'Manufacturing', text: 'OEE, line-level energy and ERP integrations.' },
      { title: 'Retail', text: 'Cold-chain monitoring and SLA tracking for refrigeration.' },
    ],
    cta: {
      title: 'Ready to transform your business?',
      text: 'Talk to CONNECT architects and spin up a demo in hours.',
      primary: 'Get started',
      secondary: 'Go to devices',
    },
    footer: 'All rights reserved.',
  },
  devices: {
    title: 'Device fleet',
    subtitle: 'Manage every device connected to your account in one view.',
    add: 'Add device',
    setup: 'Setup wizard',
    monitoring: 'Monitoring',
    refresh: 'Refresh',
    auto: 'Auto refresh every 10 s',
    loading: 'Fetching devices…',
    metrics: {
      power: 'Power',
      voltage: 'Voltage',
      energy: 'Energy',
    },
    info: {
      ip: 'IP',
      category: 'Category',
      lastSeen: 'Last seen',
      unknown: 'unknown',
    },
    actions: {
      view: 'View',
      delete: 'Delete',
    },
    emptyTitle: 'No devices yet',
    emptyText: 'Connect your first device or spawn a demo template to explore the dashboard.',
    emptyCta: 'Add device',
    modalTitle: 'Add a demo device',
    modalText: 'Pick a category and we will create a virtual device with realistic power profile.',
    close: 'Close',
    statusOnline: 'online',
    statusOffline: 'offline',
    fallbackName: 'Unnamed device',
    fallbackUid: 'UID',
    deleteConfirm: 'Delete this device?',
    messages: {
      added: 'Added demo: {name}',
      addError: 'Unable to add a demo device.',
      deleted: 'Device removed',
      deleteError: 'Unable to delete device.',
      listError: 'Failed to load devices list.',
    },
    demoCatalog: [
      { type: 'tv', name: 'Smart TV', desc: 'Samsung TV, ~120W', img: '/images/demo/tv.png' },
      { type: 'fridge', name: 'Fridge', desc: 'Bosch A++, ~50W', img: '/images/demo/fridge.png' },
      { type: 'boiler', name: 'Boiler', desc: 'Storage heater, up to 2 kW', img: '/images/demo/boiler.png' },
      { type: 'router', name: 'Router', desc: 'Wi-Fi, 10–15W', img: '/images/demo/router.png' },
    ],
  },
  monitoring: {
    ranges: [
      { label: '3h', value: 180 },
      { label: '6h', value: 360 },
      { label: '12h', value: 720 },
      { label: '1 day', value: 1440 },
      { label: '3 days', value: 4320 },
      { label: '1 week', value: 10080 },
      { label: '1 month', value: 43200 },
    ],
    overallLabel: 'Average power',
  },
}

export const translations = {
  en,
  uk: en,
}

const saved = typeof window !== 'undefined' ? localStorage.getItem(STORAGE_KEY) : null
const locale = ref(SUPPORTED.includes(saved) ? saved : 'uk')

function persistLocale(value) {
  if (typeof window !== 'undefined') {
    localStorage.setItem(STORAGE_KEY, value)
  }
  if (typeof document !== 'undefined') {
    document.documentElement.lang = value === 'uk' ? 'uk' : 'en'
  }
}

persistLocale(locale.value)

function setLocale(value) {
  if (!SUPPORTED.includes(value)) return
  locale.value = value
  persistLocale(value)
}

const currentMessages = computed(() => translations[locale.value])
const dateLocale = computed(() => (locale.value === 'uk' ? 'uk-UA' : 'en-US'))

function t(path, fallback = '') {
  const parts = path.split('.')
  let node = currentMessages.value
  for (const part of parts) {
    if (node && Object.prototype.hasOwnProperty.call(node, part)) {
      node = node[part]
    } else {
      node = undefined
      break
    }
  }
  return (typeof node === 'string' ? node : fallback) ?? fallback
}

export default function useLocale() {
  return {
    locale,
    setLocale,
    messages: currentMessages,
    t,
    dateLocale,
  }
}
