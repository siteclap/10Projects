import type { Metadata } from 'next';
import { Container } from '@/components/layout/Container';

export const metadata: Metadata = {
  title: 'Terms of Service | 10Projects',
  description:
    'Terms and conditions for using 10Projects.com, including user obligations, intellectual property, and liability.',
};

export default function TermsPage() {
  return (
    <Container>
      <div className="mx-auto max-w-narrow py-4xl">
        <h1 className="text-h1 text-gray-900">Terms of Service</h1>
        <p className="mt-lg text-sm text-gray-500">
          Last updated: August 2026
        </p>

        <div className="mt-3xl flex flex-col gap-3xl text-sm leading-relaxed text-gray-700">
          {/* Acceptance */}
          <section>
            <h2 className="text-h3 text-gray-900">Acceptance of Terms</h2>
            <p className="mt-lg">
              By accessing or using 10Projects.com (&quot;the Website&quot;),
              you agree to be bound by these Terms of Service. If you do not
              agree to these terms, please do not use the Website.
            </p>
          </section>

          {/* Description of Service */}
          <section>
            <h2 className="text-h3 text-gray-900">Description of Service</h2>
            <p className="mt-lg">
              10Projects.com is an AI-powered real estate discovery platform
              that helps buyers find their best-fit residential projects. Our
              services include property information, AI-driven Fit Scores,
              property comparisons, and connecting users with developers and
              channel partners.
            </p>
            <p className="mt-md">
              We act as an information platform only. We are not a real estate
              broker, agent, or developer, and we do not participate in any
              property transaction.
            </p>
          </section>

          {/* User Obligations */}
          <section>
            <h2 className="text-h3 text-gray-900">User Obligations</h2>
            <p className="mt-lg">By using the Website, you agree to:</p>
            <ul className="mt-md flex flex-col gap-sm list-disc pl-xl">
              <li>
                Provide accurate and complete information when submitting
                enquiries or creating an account.
              </li>
              <li>
                Use the Website for lawful purposes only and in accordance with
                these terms.
              </li>
              <li>
                Not reproduce, duplicate, copy, sell, or exploit any portion of
                the Website without express written permission.
              </li>
              <li>
                Not attempt to gain unauthorised access to any part of the
                Website, its servers, or connected systems.
              </li>
              <li>
                Not use automated tools, bots, or scrapers to extract data from
                the Website.
              </li>
            </ul>
          </section>

          {/* Intellectual Property */}
          <section>
            <h2 className="text-h3 text-gray-900">Intellectual Property</h2>
            <p className="mt-lg">
              All content on the Website — including text, graphics, logos, AI
              algorithms, scoring methodologies, design, and software — is the
              property of 10Projects or its licensors and is protected by
              intellectual property laws. You may not use, copy, or distribute
              any content without prior written consent.
            </p>
          </section>

          {/* Project Information */}
          <section>
            <h2 className="text-h3 text-gray-900">Project Information</h2>
            <p className="mt-lg">
              Project details including pricing, specifications, floor plans,
              amenities, and possession timelines are sourced from developers,
              RERA portals, and public records. While we strive for accuracy, we
              do not guarantee that all information is current, complete, or
              error-free. Users should independently verify all details before
              making any decisions. Please refer to our{' '}
              <a
                href="/disclaimer"
                className="text-brand-primary hover:underline"
              >
                Disclaimer
              </a>{' '}
              for full details.
            </p>
          </section>

          {/* Lead Sharing */}
          <section>
            <h2 className="text-h3 text-gray-900">
              Lead Sharing &amp; Communication
            </h2>
            <p className="mt-lg">
              When you submit an enquiry (e.g., request pricing, book a site
              visit, download a brochure), your contact details will be shared
              with the relevant developer or their authorised channel partners.
              By submitting an enquiry, you consent to being contacted via
              phone, SMS, email, or WhatsApp regarding the property and related
              services.
            </p>
          </section>

          {/* Limitation of Liability */}
          <section>
            <h2 className="text-h3 text-gray-900">Limitation of Liability</h2>
            <p className="mt-lg">
              To the maximum extent permitted by law, 10Projects.com shall not
              be liable for any indirect, incidental, special, consequential, or
              punitive damages arising out of or related to your use of the
              Website. Our total liability for any claims shall not exceed the
              amount paid by you (if any) for using our services.
            </p>
          </section>

          {/* Indemnification */}
          <section>
            <h2 className="text-h3 text-gray-900">Indemnification</h2>
            <p className="mt-lg">
              You agree to indemnify and hold harmless 10Projects.com, its
              directors, employees, and affiliates from any claims, damages, or
              expenses arising from your use of the Website or violation of
              these terms.
            </p>
          </section>

          {/* Third-Party Links */}
          <section>
            <h2 className="text-h3 text-gray-900">Third-Party Links</h2>
            <p className="mt-lg">
              The Website may contain links to third-party websites or services.
              We are not responsible for the content, accuracy, or practices of
              these external sites.
            </p>
          </section>

          {/* Governing Law */}
          <section>
            <h2 className="text-h3 text-gray-900">Governing Law</h2>
            <p className="mt-lg">
              These Terms shall be governed by and construed in accordance with
              the laws of India. Any disputes arising from these terms shall be
              subject to the exclusive jurisdiction of the courts in Mumbai,
              Maharashtra.
            </p>
          </section>

          {/* Changes */}
          <section>
            <h2 className="text-h3 text-gray-900">Changes to Terms</h2>
            <p className="mt-lg">
              We reserve the right to modify these Terms of Service at any time.
              Changes will be effective immediately upon posting on this page.
              Your continued use of the Website after any changes constitutes
              acceptance of the updated terms.
            </p>
          </section>

          {/* Contact */}
          <section>
            <h2 className="text-h3 text-gray-900">Contact Us</h2>
            <p className="mt-lg">
              If you have any questions about these Terms of Service, please
              contact us at{' '}
              <a
                href="mailto:legal@10projects.com"
                className="text-brand-primary hover:underline"
              >
                legal@10projects.com
              </a>
              .
            </p>
          </section>
        </div>
      </div>
    </Container>
  );
}
