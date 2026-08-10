import { PrismaClient } from '../src/generated/prisma/client.ts';
import { PrismaPg } from '@prisma/adapter-pg';
import { hash } from 'bcryptjs';
import 'dotenv/config';

const adapter = new PrismaPg({ connectionString: process.env.DATABASE_URL! });
const prisma = new PrismaClient({ adapter });

async function main() {
  console.log('Seeding database...');

  // 1. Create default tenant
  const tenant = await prisma.tenant.upsert({
    where: { slug: 'demo' },
    update: {},
    create: {
      name: 'Demo Developer',
      slug: 'demo',
      domain: 'localhost:3000',
      themeId: 'developer-starter',
      active: true,
    },
  });

  console.log(`Created tenant: ${tenant.name} (${tenant.id})`);

  // 2. Create branding for the tenant
  await prisma.tenantBranding.upsert({
    where: { tenantId: tenant.id },
    update: {},
    create: {
      tenantId: tenant.id,
      tagline: 'You need one right home — not hundreds of listings.',
      primaryColor: '#4B1CB0',
      primaryDark: '#3B1490',
      primaryLight: '#7C3AED',
      accentColor: '#F59E0B',
      heroHeading: 'Find Your Perfect Home in Navi Mumbai',
      heroSubheading: 'AI-powered recommendations from 100+ verified projects',
      ctaText: 'Find My 10',
      footerText: '© 2026 Demo Developer. All rights reserved.',
    },
  });

  console.log('Created tenant branding');

  // 3. Create contact for the tenant
  await prisma.tenantContact.upsert({
    where: { tenantId: tenant.id },
    update: {},
    create: {
      tenantId: tenant.id,
      phone: '+91 98765 43210',
      whatsapp: '+91 98765 43210',
      email: 'hello@demo-developer.com',
      address: '123 Business Park, Sector 15',
      city: 'Navi Mumbai',
      state: 'Maharashtra',
    },
  });

  console.log('Created tenant contact');

  // 4. Create social for the tenant
  await prisma.tenantSocial.upsert({
    where: { tenantId: tenant.id },
    update: {},
    create: {
      tenantId: tenant.id,
      facebook: 'https://facebook.com/demodeveloper',
      instagram: 'https://instagram.com/demodeveloper',
      linkedin: 'https://linkedin.com/company/demodeveloper',
      youtube: 'https://youtube.com/@demodeveloper',
      twitter: 'https://twitter.com/demodeveloper',
    },
  });

  console.log('Created tenant social');

  // 5. Create SEO for the tenant
  await prisma.tenantSeo.upsert({
    where: { tenantId: tenant.id },
    update: {},
    create: {
      tenantId: tenant.id,
      siteTitle: 'Demo Developer | Premium Homes in Navi Mumbai',
      metaDescription:
        'Find your dream home in Navi Mumbai. AI-powered recommendations from 100+ verified projects across Kharghar, Panvel, and more.',
      ogImageUrl: '/og-image.jpg',
      gaTrackingId: 'G-XXXXXXXXXX',
    },
  });

  console.log('Created tenant SEO');

  // 6. Create super admin user
  const hashedPassword = await hash('admin123', 12);

  const adminUser = await prisma.user.upsert({
    where: { email: 'admin@10projects.com' },
    update: {},
    create: {
      email: 'admin@10projects.com',
      password: hashedPassword,
      name: 'Super Admin',
      role: 'SUPER_ADMIN',
      tenantId: tenant.id,
      superAdmin: true,
    },
  });

  console.log(`Created admin user: ${adminUser.email} (${adminUser.id})`);

  // 7. Create sample locations
  const kharghar = await prisma.location.upsert({
    where: {
      tenantId_slug: { tenantId: tenant.id, slug: 'kharghar' },
    },
    update: {},
    create: {
      tenantId: tenant.id,
      name: 'Kharghar',
      slug: 'kharghar',
    },
  });

  const panvel = await prisma.location.upsert({
    where: {
      tenantId_slug: { tenantId: tenant.id, slug: 'panvel' },
    },
    update: {},
    create: {
      tenantId: tenant.id,
      name: 'Panvel',
      slug: 'panvel',
    },
  });

  console.log(`Created locations: ${kharghar.name}, ${panvel.name}`);

  // 8. Create sample tags
  const featuredTag = await prisma.tag.upsert({
    where: {
      tenantId_slug: { tenantId: tenant.id, slug: 'featured' },
    },
    update: {},
    create: {
      tenantId: tenant.id,
      name: 'Featured',
      slug: 'featured',
    },
  });

  const hotDealTag = await prisma.tag.upsert({
    where: {
      tenantId_slug: { tenantId: tenant.id, slug: 'hot-deal' },
    },
    update: {},
    create: {
      tenantId: tenant.id,
      name: 'Hot Deal',
      slug: 'hot-deal',
    },
  });

  console.log(`Created tags: ${featuredTag.name}, ${hotDealTag.name}`);

  // 9. Create sample projects with configurations
  const project1 = await prisma.project.upsert({
    where: {
      tenantId_slug: { tenantId: tenant.id, slug: 'skyline-heights' },
    },
    update: {},
    create: {
      tenantId: tenant.id,
      title: 'Skyline Heights',
      slug: 'skyline-heights',
      developer: 'Demo Developer',
      locationId: kharghar.id,
      constructionStage: 'Under Construction',
      expectedPossession: 'Dec 2027',
      reraNumber: 'P52000012345',
      landParcel: '5 Acres',
      floors: 'G + 35',
      description:
        'Skyline Heights offers premium 2 & 3 BHK apartments in the heart of Kharghar, with stunning views of the hills and modern amenities. Located just 10 minutes from Kharghar railway station.',
      highlights:
        'Hill view apartments, Clubhouse with infinity pool, Smart home features, EV charging stations',
      thumbnail: '/placeholder-project-1.jpg',
      priceMin: 8500000,
      priceMax: 15500000,
      amenities: [
        'Swimming Pool',
        'Gymnasium',
        'Clubhouse',
        'Children Play Area',
        'Jogging Track',
        'Garden',
        'Power Backup',
        'Lift',
        'Car Parking',
        'Security',
      ],
      pros: [
        'Excellent connectivity via Sion-Panvel Highway',
        'Close to upcoming Navi Mumbai International Airport',
        'Reputed developer with strong track record',
      ],
      cons: [
        'Construction ongoing, 2+ years to possession',
        'Premium pricing compared to nearby projects',
      ],
      published: true,
      featured: true,
      fitScore: 82,
      configurations: {
        create: [
          {
            configType: '2 BHK',
            carpetAreaSqft: 650,
            basePrice: 8500000,
            totalPrice: 9200000,
            inventoryTotal: 120,
            inventoryAvailable: 45,
          },
          {
            configType: '3 BHK',
            carpetAreaSqft: 950,
            basePrice: 13500000,
            totalPrice: 15500000,
            inventoryTotal: 80,
            inventoryAvailable: 22,
          },
        ],
      },
      tags: {
        create: [
          { tagId: featuredTag.id },
        ],
      },
    },
  });

  const project2 = await prisma.project.upsert({
    where: {
      tenantId_slug: { tenantId: tenant.id, slug: 'green-meadows' },
    },
    update: {},
    create: {
      tenantId: tenant.id,
      title: 'Green Meadows',
      slug: 'green-meadows',
      developer: 'Demo Developer',
      locationId: panvel.id,
      constructionStage: 'Ready to Move',
      expectedPossession: 'Immediate',
      reraNumber: 'P52000054321',
      landParcel: '8 Acres',
      floors: 'G + 22',
      description:
        'Green Meadows is a ready-to-move-in township in Panvel offering spacious 1, 2 & 3 BHK apartments surrounded by lush greenery. Perfect for families looking for an immediate move-in option.',
      highlights:
        'Ready to move in, 80% green cover, Township with school and retail, OC received',
      thumbnail: '/placeholder-project-2.jpg',
      priceMin: 4500000,
      priceMax: 12000000,
      amenities: [
        'Swimming Pool',
        'Gymnasium',
        'School',
        'Shopping Centre',
        'Temple',
        'Garden',
        'Power Backup',
        'Lift',
        'Car Parking',
        'Security',
        'Rain Water Harvesting',
        'Solar Panels',
      ],
      pros: [
        'Ready to move in with OC',
        'Large township with self-sufficient amenities',
        'Competitive pricing for Panvel market',
      ],
      cons: [
        'Slightly far from Panvel railway station (4 km)',
        'Some towers face the highway',
      ],
      published: true,
      featured: false,
      fitScore: 76,
      configurations: {
        create: [
          {
            configType: '1 BHK',
            carpetAreaSqft: 420,
            basePrice: 4500000,
            totalPrice: 5100000,
            inventoryTotal: 200,
            inventoryAvailable: 15,
          },
          {
            configType: '2 BHK',
            carpetAreaSqft: 680,
            basePrice: 7800000,
            totalPrice: 8500000,
            inventoryTotal: 150,
            inventoryAvailable: 32,
          },
          {
            configType: '3 BHK',
            carpetAreaSqft: 1020,
            basePrice: 10800000,
            totalPrice: 12000000,
            inventoryTotal: 60,
            inventoryAvailable: 8,
          },
        ],
      },
      tags: {
        create: [
          { tagId: hotDealTag.id },
        ],
      },
    },
  });

  console.log(`Created projects: ${project1.title}, ${project2.title}`);

  console.log('\nSeed completed successfully!');
  console.log('---');
  console.log('Login credentials:');
  console.log('  Email: admin@10projects.com');
  console.log('  Password: admin123');
  console.log('---');
}

main()
  .then(async () => {
    await prisma.$disconnect();
  })
  .catch(async (e) => {
    console.error('Seed failed:', e);
    await prisma.$disconnect();
    process.exit(1);
  });
