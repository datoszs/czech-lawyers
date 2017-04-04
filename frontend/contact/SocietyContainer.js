import {connect} from 'react-redux';
import {SocietyOverview} from '../components';
import translate from '../translate';

const mapStateToProps = (state) => ({
    name: translate.getMessage(state, 'society.name'),
    street: translate.getMessage(state, 'society.street'),
    ic: translate.getMessage(state, 'society.ic'),
    city: translate.getMessage(state, 'society.city'),
});

export default connect(mapStateToProps)(SocietyOverview);
