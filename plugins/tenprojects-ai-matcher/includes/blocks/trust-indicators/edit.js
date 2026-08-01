/**
 * Trust Indicators block — Editor view.
 *
 * @package TenProjects
 * @since 1.0.0
 */
( function () {
    var el                = wp.element.createElement;
    var useBlockProps     = wp.blockEditor.useBlockProps;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody         = wp.components.PanelBody;
    var SelectControl     = wp.components.SelectControl;
    var registerBlockType = wp.blocks.registerBlockType;

    var trustItems = [
        { title: '100% Honest Analysis', desc: 'Every project gets the same unbiased evaluation. No sponsored rankings, no hidden promotions.', icon: '\uD83D\uDD0D' },
        { title: 'RERA Verified', desc: 'We verify RERA registration, developer track records, and legal clearances before listing any project.', icon: '\uD83D\uDEE1\uFE0F' },
        { title: 'AI-Powered, Human-Verified', desc: 'Our AI analyses 20+ parameters per project. Property experts verify data and add on-ground insights.', icon: '\uD83E\uDD16' },
    ];

    registerBlockType( 'tenprojects/trust-indicators', {
        edit: function ( props ) {
            var attributes    = props.attributes;
            var setAttributes = props.setAttributes;
            var variant       = attributes.variant || 'full';
            var isCompact     = variant === 'compact';

            var blockProps = useBlockProps( {
                style: {
                    padding: isCompact ? '12px 16px' : '32px 24px',
                    backgroundColor: '#f8fafc',
                    borderRadius: '12px',
                    border: '1px solid #e2e8f0',
                },
            } );

            return el( 'div', blockProps,
                // Inspector sidebar controls.
                el( InspectorControls, null,
                    el( PanelBody, { title: 'Display Settings', initialOpen: true },
                        el( SelectControl, {
                            label: 'Variant',
                            value: variant,
                            options: [
                                { label: 'Full (with descriptions)', value: 'full' },
                                { label: 'Compact (inline)', value: 'compact' },
                            ],
                            onChange: function ( val ) {
                                setAttributes( { variant: val } );
                            },
                        } )
                    )
                ),

                // Trust items preview.
                isCompact
                    ? el( 'div', {
                        style: {
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            gap: '16px',
                            flexWrap: 'wrap',
                        },
                    }, trustItems.map( function ( item, i ) {
                        return el( 'span', {
                            key: i,
                            style: {
                                display: 'inline-flex',
                                alignItems: 'center',
                                gap: '6px',
                                fontSize: '13px',
                                fontWeight: '500',
                                color: '#475569',
                            },
                        },
                            el( 'span', null, item.icon ),
                            el( 'span', null, item.title ),
                            i < trustItems.length - 1 ? el( 'span', {
                                style: {
                                    marginLeft: '10px',
                                    color: '#cbd5e1',
                                },
                            }, '\u2022' ) : null
                        );
                    } ) )
                    : el( 'div', {
                        style: {
                            display: 'grid',
                            gridTemplateColumns: 'repeat(3, 1fr)',
                            gap: '24px',
                        },
                    }, trustItems.map( function ( item, i ) {
                        return el( 'div', {
                            key: i,
                            style: { textAlign: 'center' },
                        },
                            el( 'div', {
                                style: {
                                    fontSize: '28px',
                                    marginBottom: '8px',
                                },
                            }, item.icon ),
                            el( 'div', {
                                style: {
                                    fontSize: '14px',
                                    fontWeight: '600',
                                    color: '#1e293b',
                                    marginBottom: '4px',
                                },
                            }, item.title ),
                            el( 'div', {
                                style: {
                                    fontSize: '12px',
                                    color: '#64748b',
                                    lineHeight: '1.5',
                                },
                            }, item.desc )
                        );
                    } ) )
            );
        },
    } );
} )();
