import { Header } from '@/components/layout/Header';
import { Footer } from '@/components/layout/Footer';
import { ScrollToTop } from '@/components/layout/ScrollToTop';
import { wpFetchSiteSettings } from '@/lib/wp-api';

export default async function MarketingLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  const settings = await wpFetchSiteSettings();

  return (
    <>
      <ScrollToTop />
      <Header logoUrl={settings.logo_light} />
      <main className="flex-1">{children}</main>
      <Footer logoUrl={settings.logo_dark || settings.logo_light} settings={settings} />
    </>
  );
}
