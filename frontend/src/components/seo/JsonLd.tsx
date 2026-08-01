interface JsonLdProps {
  data: Record<string, unknown>;
}

/**
 * Server component that renders a JSON-LD structured data script tag.
 * Used for SEO: real estate listings, breadcrumbs, FAQs, organization schema.
 */
export function JsonLd({ data }: JsonLdProps) {
  return (
    <script
      type="application/ld+json"
      dangerouslySetInnerHTML={{
        __html: JSON.stringify(data, null, 0),
      }}
    />
  );
}
