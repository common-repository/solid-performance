import { registerPlugin } from '@wordpress/plugins';
import solidIcon from './icon';
import SolidMeta from './meta';
registerPlugin( 'swpsp-cache', { render: SolidMeta, icon: solidIcon } );