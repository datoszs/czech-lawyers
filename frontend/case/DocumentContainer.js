import React, {PropTypes} from 'react';
import {connect} from 'react-redux';
import {List} from 'immutable';
import {getDocuments} from './selectors';
import DocumentDetail from './DocumentDetail';

const DocumentContainer = ({documents}) => (
    <div>
        {documents.map((document) => <DocumentDetail key={document} id={document} />)}
    </div>
);

DocumentContainer.propTypes = {
    documents: PropTypes.instanceOf(List).isRequired,
};

const mapStateToProps = (state) => ({
    documents: getDocuments(state),
});

export default connect(mapStateToProps)(DocumentContainer);
