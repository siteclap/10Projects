/**
 * AI Assessment block — Editor view.
 *
 * @package TenProjects
 * @since 1.0.0
 */
( function () {
    var el            = wp.element.createElement;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var registerBlockType = wp.blocks.registerBlockType;

    registerBlockType( 'tenprojects/ai-assessment', {
        edit: function () {
            var blockProps = useBlockProps( {
                style: {
                    padding: '40px 24px',
                    backgroundColor: '#f8f9fa',
                    border: '2px dashed #c4b5fd',
                    borderRadius: '12px',
                    textAlign: 'center',
                },
            } );

            return el( 'div', blockProps,
                el( 'div', {
                    style: {
                        fontSize: '36px',
                        marginBottom: '12px',
                    },
                }, '\uD83E\uDD16' ),
                el( 'h3', {
                    style: {
                        margin: '0 0 8px',
                        fontSize: '18px',
                        fontWeight: '600',
                        color: '#1e293b',
                    },
                }, 'AI Assessment Chat' ),
                el( 'p', {
                    style: {
                        margin: '0',
                        fontSize: '14px',
                        color: '#64748b',
                    },
                }, 'This block renders the interactive assessment on the frontend.' ),
                el( 'p', {
                    style: {
                        margin: '8px 0 0',
                        fontSize: '12px',
                        color: '#94a3b8',
                    },
                }, 'Includes: progress bar, chat messages, chat input, and AI property matching flow.' )
            );
        },
    } );
} )();
