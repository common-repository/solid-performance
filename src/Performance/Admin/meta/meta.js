import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import {
	ToggleControl,
	PanelBody,
} from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import solidIcon from './icon';

const SolidExcludeMeta = () => {
    const meta = useSelect((select) => select('core/editor').getEditedPostAttribute('meta'));
    const { editPost } = useDispatch('core/editor');

	if ( meta === undefined ) {
		return null;
	}

    return (
		<>
			<PluginSidebarMoreMenuItem
				target="swpsp-cache"
				icon={ solidIcon }
			>
				{  __( 'Cache Exclusion', 'solid-performance' ) }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar
				isPinnable={ false }
				icon={ solidIcon }
				name="swpsp-cache"
				title={ __( 'Cache Exclusion', 'solid-performance' ) }
			>
				<PanelBody>
					<ToggleControl
						label={ __( 'Exclude from Page Cache', 'solid-performance' ) }
						checked={meta._swpsp_post_exclude}
						onChange={(value) => editPost({ meta: { _swpsp_post_exclude: value } })}
					/>
				</PanelBody>
			</PluginSidebar>
		</>
    );
};
export default SolidExcludeMeta;
