import type { Metadata } from 'next';
import { Container } from '@/components/layout/Container';

export const metadata: Metadata = {
  title: 'Privacy Policy | LeadMAAXX',
  description:
    'Learn how LeadMAAXX.com collects, uses, and protects your personal information.',
};

export default function PrivacyPolicyPage() {
  return (
    <Container>
      <div className="mx-auto max-w-narrow py-4xl">
        <h1 className="text-h1 text-gray-900">Privacy Policy</h1>
        <p className="mt-lg text-sm text-gray-500">
          Last updated: August 2026
        </p>

        <div className="mt-3xl flex flex-col gap-3xl text-sm leading-relaxed text-gray-700">
          {/* Introduction */}
          <section>
            <h2 className="text-h3 text-gray-900">Introduction</h2>
            <p className="mt-lg">
              LeadMAAXX.com (&quot;we&quot;, &quot;us&quot;, or &quot;our&quot;)
              is committed to protecting your privacy. This Privacy Policy
              explains how we collect, use, disclose, and safeguard your
              information when you visit our website or use our services.
            </p>
          </section>

          {/* Information We Collect */}
          <section>
            <h2 className="text-h3 text-gray-900">Information We Collect</h2>
            <h3 className="mt-lg text-base font-semibold text-gray-900">
              Personal Information
            </h3>
            <p className="mt-md">
              When you use our services, we may collect the following personal
              information:
            </p>
            <ul className="mt-md flex flex-col gap-sm list-disc pl-xl">
              <li>Name</li>
              <li>Phone number</li>
              <li>Email address</li>
              <li>Property preferences (budget, location, configuration)</li>
              <li>Assessment responses and search history</li>
            </ul>

            <h3 className="mt-xl text-base font-semibold text-gray-900">
              Automatically Collected Information
            </h3>
            <p className="mt-md">
              When you access our website, we may automatically collect:
            </p>
            <ul className="mt-md flex flex-col gap-sm list-disc pl-xl">
              <li>
                Device information (browser type, operating system, device type)
              </li>
              <li>IP address and approximate location</li>
              <li>Pages visited, time spent, and interaction data</li>
              <li>Referral source and search terms</li>
              <li>Cookies and similar tracking technologies</li>
            </ul>
          </section>

          {/* How We Use Your Information */}
          <section>
            <h2 className="text-h3 text-gray-900">
              How We Use Your Information
            </h2>
            <p className="mt-lg">
              We use the information we collect for the following purposes:
            </p>
            <ul className="mt-md flex flex-col gap-sm list-disc pl-xl">
              <li>
                To provide AI-powered property recommendations and Fit Scores
              </li>
              <li>
                To connect you with property advisors, developers, and channel
                partners
              </li>
              <li>To schedule site visits and share project brochures</li>
              <li>
                To send relevant property alerts, offers, and updates via phone,
                SMS, email, or WhatsApp
              </li>
              <li>To improve our website, services, and user experience</li>
              <li>To analyse usage trends and optimise our platform</li>
              <li>To comply with legal obligations</li>
            </ul>
          </section>

          {/* Information Sharing */}
          <section>
            <h2 className="text-h3 text-gray-900">Information Sharing</h2>
            <p className="mt-lg">
              We may share your information with the following parties:
            </p>
            <ul className="mt-md flex flex-col gap-sm list-disc pl-xl">
              <li>
                <strong>Developers &amp; Channel Partners:</strong> When you
                express interest in a project, your contact details may be
                shared with the relevant developer or their authorised channel
                partners to facilitate your enquiry.
              </li>
              <li>
                <strong>Service Providers:</strong> Third-party vendors who
                assist us with analytics, communication, hosting, and other
                operational services.
              </li>
              <li>
                <strong>Legal Requirements:</strong> When required by law,
                regulation, court order, or governmental authority.
              </li>
            </ul>
            <p className="mt-md">
              We do not sell your personal information to third parties for
              their marketing purposes.
            </p>
          </section>

          {/* Cookies */}
          <section>
            <h2 className="text-h3 text-gray-900">
              Cookies &amp; Tracking Technologies
            </h2>
            <p className="mt-lg">
              We use cookies and similar technologies to enhance your browsing
              experience, analyse site traffic, and personalise content. You can
              control cookie preferences through your browser settings. Please
              note that disabling cookies may limit some website functionality.
            </p>
          </section>

          {/* Data Security */}
          <section>
            <h2 className="text-h3 text-gray-900">Data Security</h2>
            <p className="mt-lg">
              We implement appropriate technical and organisational measures to
              protect your personal information against unauthorised access,
              alteration, disclosure, or destruction. However, no method of
              electronic transmission or storage is 100% secure, and we cannot
              guarantee absolute security.
            </p>
          </section>

          {/* Data Retention */}
          <section>
            <h2 className="text-h3 text-gray-900">Data Retention</h2>
            <p className="mt-lg">
              We retain your personal information for as long as necessary to
              fulfil the purposes outlined in this policy, or as required by
              law. You may request deletion of your data at any time by
              contacting us.
            </p>
          </section>

          {/* Your Rights */}
          <section>
            <h2 className="text-h3 text-gray-900">Your Rights</h2>
            <p className="mt-lg">You have the right to:</p>
            <ul className="mt-md flex flex-col gap-sm list-disc pl-xl">
              <li>Access the personal information we hold about you</li>
              <li>Request correction of inaccurate information</li>
              <li>Request deletion of your personal data</li>
              <li>
                Opt out of marketing communications at any time
              </li>
              <li>Withdraw consent for data processing</li>
            </ul>
            <p className="mt-md">
              To exercise any of these rights, please contact us at the email
              below.
            </p>
          </section>

          {/* Third-Party Links */}
          <section>
            <h2 className="text-h3 text-gray-900">Third-Party Links</h2>
            <p className="mt-lg">
              Our website may contain links to third-party websites. We are not
              responsible for the privacy practices or content of these external
              sites. We encourage you to read the privacy policies of any
              third-party websites you visit.
            </p>
          </section>

          {/* Changes */}
          <section>
            <h2 className="text-h3 text-gray-900">Changes to This Policy</h2>
            <p className="mt-lg">
              We may update this Privacy Policy from time to time. Any changes
              will be posted on this page with an updated revision date. We
              encourage you to review this policy periodically.
            </p>
          </section>

          {/* Contact */}
          <section>
            <h2 className="text-h3 text-gray-900">Contact Us</h2>
            <p className="mt-lg">
              If you have any questions about this Privacy Policy, please
              contact us at{' '}
              <a
                href="mailto:privacy@leadmaaxx.com"
                className="text-brand-primary hover:underline"
              >
                privacy@leadmaaxx.com
              </a>
              .
            </p>
          </section>
        </div>
      </div>
    </Container>
  );
}
