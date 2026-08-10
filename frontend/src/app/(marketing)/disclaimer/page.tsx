import type { Metadata } from 'next';
import { Container } from '@/components/layout/Container';

export const metadata: Metadata = {
  title: 'Disclaimer | 10Projects',
  description:
    'Important disclaimers regarding the use of 10Projects.com, project information accuracy, and RERA compliance.',
};

export default function DisclaimerPage() {
  return (
    <Container>
      <div className="mx-auto max-w-narrow py-4xl">
        <h1 className="text-h1 text-gray-900">Disclaimer</h1>
        <p className="mt-lg text-sm text-gray-500">
          Last updated: August 2026
        </p>

        <div className="mt-3xl flex flex-col gap-3xl text-sm leading-relaxed text-gray-700">
          {/* Terms of Use */}
          <section>
            <h2 className="text-h3 text-gray-900">Terms of Use</h2>
            <p className="mt-lg">
              10Projects.com functions as an information and advertising platform
              and does not facilitate direct transactions between developers and
              website users. By using this website, you agree to the following
              terms.
            </p>
          </section>

          {/* Information Accuracy */}
          <section>
            <h2 className="text-h3 text-gray-900">Information Accuracy</h2>
            <ol className="mt-lg flex flex-col gap-md list-[lower-alpha] pl-xl">
              <li>
                All project information — written descriptions, visual media,
                pricing, floor plans, and specifications — originates from
                developers, RERA portals, and publicly available sources. This
                information is presented as-is on the platform and may not
                always be current or complete.
              </li>
              <li>
                Visual representations, including photographs, artist
                renderings, and 3D views, may not accurately reflect actual
                properties. Depictions of furnishings, surroundings, views, and
                infrastructure are illustrative only and should not be assumed
                as included features.
              </li>
              <li>
                Prices, configurations, availability, and offers displayed on
                the website are indicative and subject to change without prior
                notice. Please verify the latest pricing and availability
                directly with the developer.
              </li>
              <li>
                Users should exercise due diligence by independently verifying
                all details, including RERA registration, approvals,
                specifications, and legal documentation, before making any
                purchase or investment decisions.
              </li>
            </ol>
          </section>

          {/* RERA Compliance */}
          <section>
            <h2 className="text-h3 text-gray-900">RERA Compliance</h2>
            <p className="mt-lg">
              Display of project information on 10Projects.com does not confirm
              or guarantee a developer&apos;s registration or compliance with the
              Real Estate (Regulation and Development) Act, 2016. RERA
              registration numbers displayed are sourced from public records and
              should be verified on the respective state RERA authority website.
            </p>
          </section>

          {/* AI Scoring & Fit Score */}
          <section>
            <h2 className="text-h3 text-gray-900">
              AI Scoring &amp; Fit Score
            </h2>
            <p className="mt-lg">
              The Fit Score and AI-powered analysis provided on 10Projects.com
              are generated using proprietary algorithms based on publicly
              available data. These scores are intended as a decision-support
              tool only and should not be considered as professional real estate
              advice. Scores may change as new data becomes available.
            </p>
          </section>

          {/* Limitation of Liability */}
          <section>
            <h2 className="text-h3 text-gray-900">Limitation of Liability</h2>
            <p className="mt-lg">
              10Projects.com and its stakeholders — including promoters,
              directors, employees, and affiliates — disclaim responsibility
              for:
            </p>
            <ul className="mt-md flex flex-col gap-sm list-disc pl-xl">
              <li>
                Data inaccuracies, omissions, or errors in project information
                displayed on the platform.
              </li>
              <li>
                Any losses, damages, or expenses resulting from reliance on
                information available on the portal.
              </li>
              <li>
                Developer failures regarding service delivery, amenities,
                construction quality, possession timelines, or any other
                commitments.
              </li>
              <li>
                Third-party content, links, or services accessible through the
                platform.
              </li>
            </ul>
          </section>

          {/* Communication */}
          <section>
            <h2 className="text-h3 text-gray-900">Communication</h2>
            <p className="mt-lg">
              By submitting your contact information on 10Projects.com, you
              consent to be contacted by our team or partner agents via phone,
              SMS, email, or WhatsApp for the purpose of providing property
              information, scheduling site visits, and sharing relevant offers.
              You may opt out of communications at any time by contacting us.
            </p>
          </section>

          {/* Contact */}
          <section>
            <h2 className="text-h3 text-gray-900">Contact Us</h2>
            <p className="mt-lg">
              If you have any questions about this disclaimer, please contact us
              at{' '}
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
