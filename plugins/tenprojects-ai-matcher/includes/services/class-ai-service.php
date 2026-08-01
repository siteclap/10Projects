<?php
/**
 * AI Service — provider-agnostic abstraction for AI operations.
 *
 * Handles requirement extraction, explanation generation, and comparison summaries.
 * AI does NOT rank projects — ranking is deterministic via the Scoring Engine.
 * AI explains scores and extracts structured data from natural language.
 *
 * @package TenProjects
 * @since 1.0.0
 */

namespace TenProjects\Services;

defined( 'ABSPATH' ) || exit;

class AI_Service {

	/**
	 * Current provider instance.
	 *
	 * @var AI_Provider_Claude|AI_Provider_OpenAI|AI_Provider_Gemini|null
	 */
	private $provider = null;

	/**
	 * Provider fallback order.
	 *
	 * @var string[]
	 */
	private const FALLBACK_ORDER = array( 'claude', 'openai', 'gemini' );

	/**
	 * Category labels for human-readable output.
	 *
	 * @var array
	 */
	private const CATEGORY_LABELS = array(
		'budget_fit'              => 'Budget Match',
		'location_fit'            => 'Location Match',
		'configuration_fit'       => 'Configuration Match',
		'carpet_area_fit'         => 'Size Match',
		'possession_fit'          => 'Possession Timeline',
		'emi_fit'                 => 'EMI Affordability',
		'commute_fit'             => 'Commute Convenience',
		'lifestyle_fit'           => 'Lifestyle & Amenities',
		'developer_reliability'   => 'Developer Trust',
		'construction_stage'      => 'Construction Progress',
		'legal_confidence'        => 'Legal Safety',
		'resale_liquidity'        => 'Resale Potential',
		'rental_potential'        => 'Rental Income',
		'appreciation_drivers'    => 'Growth Potential',
		'risk_compatibility'      => 'Risk Match',
		'infrastructure_potential' => 'Infrastructure',
		'family_suitability'      => 'Family Friendliness',
		'urgency_match'           => 'Timeline Fit',
		'inventory_availability'  => 'Availability',
		'proximity_score'         => 'Nearby Essentials',
	);

	/**
	 * Constructor — loads the configured provider.
	 */
	public function __construct() {
		$this->provider = $this->load_provider();
	}

	/**
	 * Get the current provider instance.
	 *
	 * @return AI_Provider_Claude|AI_Provider_OpenAI|AI_Provider_Gemini|null
	 */
	public function get_provider() {
		return $this->provider;
	}

	/**
	 * Generate a "Why this project fits you" explanation.
	 *
	 * Takes a project's score breakdown and the customer's requirement profile,
	 * then generates a personalized narrative explanation of the match.
	 *
	 * @param array $project    Project data: id, title, rank, final_score, scores, strengths, tradeoffs, project_meta.
	 * @param array $requirement Customer requirement profile.
	 * @param array $emi_data    EMI breakdown from EMI_Calculator::breakdown().
	 * @return string|null AI-generated explanation or null on failure.
	 */
	public function generate_explanation( array $project, array $requirement, array $emi_data = array() ) {
		$system_prompt = $this->build_explanation_system_prompt();
		$user_message  = $this->build_explanation_user_message( $project, $requirement, $emi_data );

		$messages = array(
			array(
				'role'    => 'user',
				'content' => $user_message,
			),
		);

		return $this->call_with_fallback( $system_prompt, $messages, array(
			'temperature' => 0.7,
			'max_tokens'  => 1024,
		) );
	}

	/**
	 * Extract structured requirements from free-text user notes.
	 *
	 * Used for the "anything else you want us to know?" question in Phase 3.
	 * Parses natural language into structured fields the scoring engine can use.
	 *
	 * @param string $free_text User's free-text notes.
	 * @return array|null Structured requirements or null on failure.
	 */
	public function extract_requirements( string $free_text ) {
		if ( empty( trim( $free_text ) ) ) {
			return array();
		}

		$system_prompt = $this->build_extraction_system_prompt();
		$user_message  = 'Extract structured requirements from this buyer note: "' . $free_text . '"';

		$messages = array(
			array(
				'role'    => 'user',
				'content' => $user_message,
			),
		);

		$response = $this->call_with_fallback( $system_prompt, $messages, array(
			'temperature' => 0.3,
			'max_tokens'  => 512,
		) );

		if ( null === $response ) {
			return null;
		}

		// Parse JSON from response.
		$decoded = json_decode( $response, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			// Try to extract JSON from markdown code block.
			if ( preg_match( '/```(?:json)?\s*(\{[\s\S]*?\})\s*```/', $response, $matches ) ) {
				$decoded = json_decode( $matches[1], true );
			}
		}

		return is_array( $decoded ) ? $decoded : null;
	}

	/**
	 * Generate a comparison summary for 2-4 projects.
	 *
	 * Creates a structured comparison highlighting relative strengths,
	 * trade-offs, and a recommendation based on the customer profile.
	 *
	 * @param array $projects    Array of 2-4 projects, each with: title, rank, final_score, scores, strengths, tradeoffs, project_meta.
	 * @param array $requirement Customer requirement profile.
	 * @return string|null Comparison summary or null on failure.
	 */
	public function generate_comparison_summary( array $projects, array $requirement ) {
		if ( count( $projects ) < 2 || count( $projects ) > 4 ) {
			return null;
		}

		$system_prompt = $this->build_comparison_system_prompt();
		$user_message  = $this->build_comparison_user_message( $projects, $requirement );

		$messages = array(
			array(
				'role'    => 'user',
				'content' => $user_message,
			),
		);

		return $this->call_with_fallback( $system_prompt, $messages, array(
			'temperature' => 0.7,
			'max_tokens'  => 1536,
		) );
	}

	/**
	 * Call AI with automatic provider fallback.
	 *
	 * Tries the primary provider first. On failure, iterates through
	 * remaining providers in FALLBACK_ORDER.
	 *
	 * @param string $system_prompt System prompt.
	 * @param array  $messages      Messages array.
	 * @param array  $options       Options (temperature, max_tokens).
	 * @return string|null Response text or null if all providers fail.
	 */
	private function call_with_fallback( string $system_prompt, array $messages, array $options = array() ) {
		// Try primary provider first.
		if ( $this->provider && $this->provider->is_available() ) {
			try {
				$response = $this->provider->chat( $system_prompt, $messages, $options );
				if ( null !== $response && '' !== $response ) {
					return $response;
				}
			} catch ( \Exception $e ) {
				$this->log_error( 'Primary provider failed', $e );
			}
		}

		// Try fallback providers.
		$primary_slug = get_option( 'tp_ai_provider', 'claude' );

		foreach ( self::FALLBACK_ORDER as $slug ) {
			if ( $slug === $primary_slug ) {
				continue; // Already tried.
			}

			$fallback = $this->create_provider( $slug );
			if ( $fallback && $fallback->is_available() ) {
				try {
					$response = $fallback->chat( $system_prompt, $messages, $options );
					if ( null !== $response && '' !== $response ) {
						$this->log_error( 'Used fallback provider: ' . $slug );
						return $response;
					}
				} catch ( \Exception $e ) {
					$this->log_error( 'Fallback provider ' . $slug . ' failed', $e );
				}
			}
		}

		$this->log_error( 'All AI providers failed' );
		return null;
	}

	/**
	 * Load the configured AI provider.
	 *
	 * @return AI_Provider_Claude|AI_Provider_OpenAI|AI_Provider_Gemini|null
	 */
	private function load_provider() {
		$slug = get_option( 'tp_ai_provider', 'claude' );
		return $this->create_provider( $slug );
	}

	/**
	 * Create a provider instance by slug.
	 *
	 * @param string $slug Provider slug: 'claude', 'openai', or 'gemini'.
	 * @return AI_Provider_Claude|AI_Provider_OpenAI|AI_Provider_Gemini|null
	 */
	private function create_provider( string $slug ) {
		switch ( $slug ) {
			case 'claude':
				return new AI_Provider_Claude();
			case 'openai':
				return new AI_Provider_OpenAI();
			case 'gemini':
				return new AI_Provider_Gemini();
			default:
				$this->log_error( 'Unknown AI provider: ' . $slug );
				return null;
		}
	}

	/**
	 * Build the system prompt for explanation generation.
	 *
	 * @return string
	 */
	private function build_explanation_system_prompt() {
		return 'You are an expert Indian real estate advisor for 10Projects.com, a platform that helps home buyers in Navi Mumbai find their best-fit projects.

Your task is to write a personalized "Why this project fits you" explanation for a specific buyer based on their requirements and the project\'s Fit Score breakdown across 20 categories.

IMPORTANT RULES:
- Write in second person ("you", "your") — address the buyer directly.
- Be specific with numbers: prices in lakhs/crores (use Indian numbering: ₹85L, ₹1.2Cr), distances in km, EMI in ₹/month.
- Be honest about trade-offs — buyers trust transparency more than hype.
- Never fabricate data. Only reference information provided in the input.
- Keep the tone warm but professional — like a knowledgeable friend, not a salesperson.
- Format currency as: ₹50L (lakhs), ₹1.2Cr (crores), ₹45,000/month (EMI).
- Do NOT use generic filler phrases. Every sentence should contain specific, useful information.
- Keep the entire response under 250 words.

OUTPUT FORMAT (use this exact structure with markdown bold headers):

**Why [Project Name] ranked #[rank] for you:**
[1-2 sentence summary of overall fit, referencing the Fit Score percentage]

**Strongest matches:** [Top 2-3 strengths with specifics — e.g., "Budget (₹78L is well within your ₹85L comfort zone)", "Location (Kharghar — your #1 preferred area)"]

**Trade-off to consider:** [1-2 honest trade-offs with context — e.g., "Construction is 45% complete, so possession is expected Dec 2027 — 6 months beyond your ideal timeline. However, this also means you can customize interiors."]

**Estimated EMI:** ₹[amount]/month (based on 80% LTV, 8.5% interest, 20-year tenure)

**What to verify before booking:** [2-3 specific action items — e.g., "Confirm RERA status at MahaRERA portal", "Visit site to check construction progress", "Ask about payment plan flexibility"]';
	}

	/**
	 * Build the user message for explanation generation.
	 *
	 * @param array $project     Project data.
	 * @param array $requirement Customer requirements.
	 * @param array $emi_data    EMI breakdown.
	 * @return string
	 */
	private function build_explanation_user_message( array $project, array $requirement, array $emi_data ) {
		$lines = array();

		$lines[] = '=== PROJECT DATA ===';
		$lines[] = 'Name: ' . ( $project['title'] ?? 'Unknown' );
		$lines[] = 'Rank: #' . ( $project['rank'] ?? '?' );
		$lines[] = 'Fit Score: ' . ( $project['final_score'] ?? 0 ) . '/100';

		// Score breakdown.
		if ( ! empty( $project['scores'] ) ) {
			$lines[] = '';
			$lines[] = '--- Score Breakdown (0-100 per category) ---';
			foreach ( $project['scores'] as $category => $score ) {
				$label   = self::CATEGORY_LABELS[ $category ] ?? $category;
				$lines[] = $label . ': ' . $score . '/100';
			}
		}

		// Strengths.
		if ( ! empty( $project['strengths'] ) ) {
			$lines[] = '';
			$lines[] = '--- Top Strengths ---';
			foreach ( $project['strengths'] as $s ) {
				$lines[] = '- ' . ( $s['label'] ?? $s['category'] ) . ': ' . $s['score'] . '/100';
			}
		}

		// Trade-offs.
		if ( ! empty( $project['tradeoffs'] ) ) {
			$lines[] = '';
			$lines[] = '--- Trade-offs ---';
			foreach ( $project['tradeoffs'] as $t ) {
				$lines[] = '- ' . ( $t['label'] ?? $t['category'] ) . ': ' . $t['score'] . '/100';
			}
		}

		// Project meta.
		if ( ! empty( $project['project_meta'] ) ) {
			$meta    = $project['project_meta'];
			$lines[] = '';
			$lines[] = '--- Project Details ---';

			if ( ! empty( $meta['developer'] ) ) {
				$lines[] = 'Developer: ' . $meta['developer'];
			}
			if ( ! empty( $meta['location'] ) ) {
				$lines[] = 'Location: ' . $meta['location'];
			}
			if ( ! empty( $meta['configurations'] ) ) {
				$lines[] = 'Configurations: ' . implode( ', ', (array) $meta['configurations'] );
			}
			if ( ! empty( $meta['price_min'] ) || ! empty( $meta['price_max'] ) ) {
				$lines[] = 'Price Range: ' . $this->format_price( $meta['price_min'] ?? 0 )
						 . ' - ' . $this->format_price( $meta['price_max'] ?? 0 );
			}
			if ( ! empty( $meta['construction_stage'] ) ) {
				$lines[] = 'Construction Stage: ' . $meta['construction_stage'];
			}
			if ( ! empty( $meta['expected_possession'] ) ) {
				$lines[] = 'Expected Possession: ' . $meta['expected_possession'];
			}
			if ( ! empty( $meta['rera_number'] ) ) {
				$lines[] = 'RERA: ' . $meta['rera_number'];
			}
			if ( ! empty( $meta['railway_distance_km'] ) ) {
				$lines[] = 'Nearest Railway Station: ' . $meta['railway_distance_km'] . ' km';
			}
		}

		// EMI data.
		if ( ! empty( $emi_data ) ) {
			$lines[] = '';
			$lines[] = '--- EMI Estimate ---';
			$lines[] = 'Property Price: ' . $this->format_price( $emi_data['property_price'] ?? 0 );
			$lines[] = 'Down Payment: ' . $this->format_price( $emi_data['down_payment'] ?? 0 );
			$lines[] = 'Loan Amount: ' . $this->format_price( $emi_data['loan_amount'] ?? 0 );
			$lines[] = 'Monthly EMI: ' . $this->format_currency( $emi_data['monthly_emi'] ?? 0 );
			$lines[] = 'Interest Rate: ' . ( $emi_data['interest_rate'] ?? '8.5' ) . '%';
			$lines[] = 'Tenure: ' . ( $emi_data['tenure_years'] ?? 20 ) . ' years';
		}

		// Customer requirements.
		$lines[] = '';
		$lines[] = '=== CUSTOMER REQUIREMENTS ===';

		$req_fields = array(
			'city'                  => 'City',
			'configurations'        => 'Configurations',
			'preferred_locations'   => 'Preferred Locations',
			'budget_comfortable'    => 'Comfortable Budget',
			'budget_maximum'        => 'Maximum Budget',
			'purpose'               => 'Purpose',
			'purchase_timeline'     => 'Timeline',
			'funding_mode'          => 'Funding',
			'priorities'            => 'Top Priorities',
			'emi_comfort_max'       => 'Max Comfortable EMI',
			'possession_tolerance'  => 'Possession Tolerance',
			'must_haves'            => 'Must-Haves',
			'carpet_area_min'       => 'Min Carpet Area (sq ft)',
			'carpet_area_max'       => 'Max Carpet Area (sq ft)',
			'family_members'        => 'Family Members',
			'commute_workplace'     => 'Workplace Location',
			'risk_tolerance'        => 'Risk Tolerance',
		);

		foreach ( $req_fields as $key => $label ) {
			if ( ! empty( $requirement[ $key ] ) ) {
				$value = $requirement[ $key ];
				if ( is_array( $value ) ) {
					$value = implode( ', ', $value );
				}
				if ( in_array( $key, array( 'budget_comfortable', 'budget_maximum', 'emi_comfort_max' ), true ) ) {
					$value = $this->format_price( $value );
				}
				$lines[] = $label . ': ' . $value;
			}
		}

		return implode( "\n", $lines );
	}

	/**
	 * Build the system prompt for requirement extraction.
	 *
	 * @return string
	 */
	private function build_extraction_system_prompt() {
		return 'You are a requirement parser for an Indian real estate platform (10Projects.com, Navi Mumbai).

Your task is to extract structured property requirements from a buyer\'s free-text notes. The buyer has already answered structured questions about budget, location, and configuration. This free-text captures additional preferences they mentioned in the "anything else" field.

Parse the text and return a JSON object with ONLY the fields you can confidently extract. Do not guess or fabricate values. If a field is not mentioned, do not include it.

Possible fields:
{
  "floor_preference": "low|mid|high|top|any",
  "facing_preference": "east|west|north|south|any",
  "vastu_required": true|false,
  "parking_count": 1|2|3,
  "servant_room": true|false,
  "study_room": true|false,
  "garden_terrace": true|false,
  "pet_friendly": true|false,
  "senior_friendly": true|false,
  "nearby_requirements": ["school", "hospital", "metro", "market", "temple", "park"],
  "deal_breakers": ["near highway", "no lift", "shared wall"],
  "construction_preference": "ready|under_construction|any",
  "developer_preference": "branded|any",
  "view_preference": "garden|sea|city|mountain|pool|any",
  "additional_notes": "any remaining text that does not fit above fields"
}

RULES:
- Return ONLY valid JSON. No explanation, no markdown, no extra text.
- Use exact field names and value options shown above.
- Convert Hindi/Marathi terms to English equivalents (e.g., "pooja room" → study_room or note it).
- "Near station" → nearby_requirements: ["metro"] or ["railway"].
- Be conservative: if unsure, put it in additional_notes rather than guessing a structured field.';
	}

	/**
	 * Build the system prompt for comparison summaries.
	 *
	 * @return string
	 */
	private function build_comparison_system_prompt() {
		return 'You are an expert Indian real estate advisor for 10Projects.com, helping buyers compare shortlisted projects in Navi Mumbai.

Your task is to write a clear, structured comparison of 2-4 projects based on their Fit Scores and the buyer\'s requirements.

IMPORTANT RULES:
- Be objective and data-driven. Reference specific scores and numbers.
- Highlight where each project is strongest relative to the others.
- Be honest about trade-offs — do not favour any project unfairly.
- Use Indian real estate terminology and currency (₹ lakhs/crores).
- Address the buyer directly ("you", "your").
- Keep the total response under 400 words.

OUTPUT FORMAT:

**Quick Comparison: [Project Names]**

**At a Glance:**
| Aspect | [Project 1] | [Project 2] | [Project 3 if applicable] | [Project 4 if applicable] |
|--------|------------|------------|--------------------------|--------------------------|
| Fit Score | X/100 | Y/100 | ... | ... |
| Price Range | ₹XL-YL | ... | ... | ... |
| Location | ... | ... | ... | ... |
| Possession | ... | ... | ... | ... |

**Where each project wins:**
- [Project 1]: [1-2 specific advantages]
- [Project 2]: [1-2 specific advantages]
- ...

**Key trade-offs:**
- [1-2 sentences about the most important differences]

**Bottom line:**
[2-3 sentences with a personalized recommendation based on the buyer\'s priorities — e.g., "If timeline is your top concern, Project A\'s ready-possession gives it an edge. But if budget matters more, Project B saves you ₹12L with comparable quality."]';
	}

	/**
	 * Build the user message for comparison generation.
	 *
	 * @param array $projects    Projects to compare.
	 * @param array $requirement Customer requirements.
	 * @return string
	 */
	private function build_comparison_user_message( array $projects, array $requirement ) {
		$lines = array();

		$lines[] = '=== PROJECTS TO COMPARE ===';
		$lines[] = '';

		foreach ( $projects as $i => $project ) {
			$num     = $i + 1;
			$lines[] = '--- Project ' . $num . ': ' . ( $project['title'] ?? 'Unknown' ) . ' ---';
			$lines[] = 'Rank: #' . ( $project['rank'] ?? '?' );
			$lines[] = 'Fit Score: ' . ( $project['final_score'] ?? 0 ) . '/100';

			if ( ! empty( $project['scores'] ) ) {
				$lines[] = 'Score Breakdown:';
				foreach ( $project['scores'] as $category => $score ) {
					$label   = self::CATEGORY_LABELS[ $category ] ?? $category;
					$lines[] = '  ' . $label . ': ' . $score . '/100';
				}
			}

			if ( ! empty( $project['strengths'] ) ) {
				$strengths = array_map( function( $s ) {
					return ( $s['label'] ?? $s['category'] ) . ' (' . $s['score'] . ')';
				}, $project['strengths'] );
				$lines[] = 'Strengths: ' . implode( ', ', $strengths );
			}

			if ( ! empty( $project['tradeoffs'] ) ) {
				$tradeoffs = array_map( function( $t ) {
					return ( $t['label'] ?? $t['category'] ) . ' (' . $t['score'] . ')';
				}, $project['tradeoffs'] );
				$lines[] = 'Trade-offs: ' . implode( ', ', $tradeoffs );
			}

			if ( ! empty( $project['project_meta'] ) ) {
				$meta = $project['project_meta'];
				if ( ! empty( $meta['developer'] ) ) {
					$lines[] = 'Developer: ' . $meta['developer'];
				}
				if ( ! empty( $meta['location'] ) ) {
					$lines[] = 'Location: ' . $meta['location'];
				}
				if ( ! empty( $meta['price_min'] ) || ! empty( $meta['price_max'] ) ) {
					$lines[] = 'Price: ' . $this->format_price( $meta['price_min'] ?? 0 )
							 . ' - ' . $this->format_price( $meta['price_max'] ?? 0 );
				}
				if ( ! empty( $meta['expected_possession'] ) ) {
					$lines[] = 'Possession: ' . $meta['expected_possession'];
				}
				if ( ! empty( $meta['construction_stage'] ) ) {
					$lines[] = 'Construction: ' . $meta['construction_stage'];
				}
			}

			$lines[] = '';
		}

		// Customer requirements summary.
		$lines[] = '=== BUYER REQUIREMENTS ===';

		if ( ! empty( $requirement['budget_comfortable'] ) ) {
			$lines[] = 'Comfortable Budget: ' . $this->format_price( $requirement['budget_comfortable'] );
		}
		if ( ! empty( $requirement['budget_maximum'] ) ) {
			$lines[] = 'Maximum Budget: ' . $this->format_price( $requirement['budget_maximum'] );
		}
		if ( ! empty( $requirement['preferred_locations'] ) ) {
			$locs = is_array( $requirement['preferred_locations'] )
				? implode( ', ', $requirement['preferred_locations'] )
				: $requirement['preferred_locations'];
			$lines[] = 'Preferred Locations: ' . $locs;
		}
		if ( ! empty( $requirement['purpose'] ) ) {
			$lines[] = 'Purpose: ' . $requirement['purpose'];
		}
		if ( ! empty( $requirement['priorities'] ) ) {
			$prio = is_array( $requirement['priorities'] )
				? implode( ', ', $requirement['priorities'] )
				: $requirement['priorities'];
			$lines[] = 'Top Priorities: ' . $prio;
		}
		if ( ! empty( $requirement['purchase_timeline'] ) ) {
			$lines[] = 'Timeline: ' . $requirement['purchase_timeline'];
		}

		return implode( "\n", $lines );
	}

	/**
	 * Format price in Indian notation (lakhs/crores).
	 *
	 * @param int|float $amount Amount in rupees.
	 * @return string Formatted price.
	 */
	private function format_price( $amount ) {
		$amount = (float) $amount;

		if ( $amount <= 0 ) {
			return 'N/A';
		}

		if ( $amount >= 10000000 ) {
			return '₹' . round( $amount / 10000000, 2 ) . 'Cr';
		}

		if ( $amount >= 100000 ) {
			return '₹' . round( $amount / 100000, 1 ) . 'L';
		}

		return '₹' . number_format( $amount );
	}

	/**
	 * Format currency with commas (Indian style for EMI-range amounts).
	 *
	 * @param int|float $amount Amount in rupees.
	 * @return string Formatted amount.
	 */
	private function format_currency( $amount ) {
		$amount = (int) $amount;

		if ( $amount <= 0 ) {
			return 'N/A';
		}

		return '₹' . number_format( $amount ) . '/month';
	}

	/**
	 * Log an AI service error.
	 *
	 * @param string          $message Error message.
	 * @param \Exception|null $e       Exception (optional).
	 */
	private function log_error( string $message, \Exception $e = null ) {
		$log = '[TenProjects AI] ' . $message;
		if ( $e ) {
			$log .= ' — ' . $e->getMessage();
		}

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( $log ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
