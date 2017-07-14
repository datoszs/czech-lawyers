import {connect} from 'react-redux';
import {Text} from '../../components';
import {getName} from './selectors';

const mapStateToProps = (state, {id}) => ({
    text: getName(state, id),
});

export default connect(mapStateToProps)(Text);
