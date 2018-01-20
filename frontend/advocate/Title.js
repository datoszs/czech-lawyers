import {connect} from 'react-redux';
import {PageTitle} from '../containers';
import {getAdvocate} from './selectors';

const mapStateToProps = (state) => {
    const advocate = getAdvocate(state);
    if (advocate) {
        return {children: advocate.name};
    } else {
        return {msg: 'advocate.detail.title'};
    }
};

export default connect(mapStateToProps)(PageTitle);
