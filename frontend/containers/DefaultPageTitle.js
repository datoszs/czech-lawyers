import {connect} from 'react-redux';
import {PageTitle} from '../components';
import translate from '../translate';

const mapStateToProps = (state) => ({
    children: translate.getMessage(state, 'title.simple'),
});

export default connect(mapStateToProps)(PageTitle);
