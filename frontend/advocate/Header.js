import {connect} from 'react-redux';
import {PageHeader} from 'react-bootstrap';
import {getAdvocate} from './selectors';
import translate from '../translate';

const mapStateToProps = (state) => {
    const advocate = getAdvocate(state);
    if (advocate) {
        return {children: advocate.name};
    } else {
        return {children: translate.getMessage(state, 'advocate.detail.title')};
    }
};

const mergeProps = ({children}) => ({children});

export default connect(mapStateToProps, undefined, mergeProps)(PageHeader);
