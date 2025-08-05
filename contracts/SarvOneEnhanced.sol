// SPDX-License-Identifier: MIT
pragma solidity ^0.8.20;

contract SarvOneEnhanced {
    struct VC {
        bytes32 hash;
        string vcType;
        uint256 issuedAt;
        string issuerDID;
        bool revoked;
        string propertyId; // For property-related VCs
        bytes32[] relatedVCs; // Array of related VC hashes
    }

    struct VCRelationship {
        bytes32 relatedVCId;
        string relationshipType; // "encumbrance", "collateral", "insurance", "verification"
        uint256 createdAt;
        string issuerDID;
        bool active;
        string metadata; // Additional relationship data (IPFS hash)
    }

    struct Organization {
        bool approved;
        address mainAddress;
        string[] scopes;
        uint256 trustScore; // 0-100 trust score
        uint256 approvedAt;
        string orgType; // "government", "bank", "insurance", "private"
    }

    struct AccessLog {
        address accessor;
        bytes32 vcHash;
        string scope;
        uint256 timestamp;
        bool success;
        string reason;
    }

    address public admin;
    uint256 public totalVCs;
    uint256 public totalOrganizations;
    uint256 public totalRelationships;

    // Primary mappings
    mapping(string => Organization) private organizations;
    mapping(address => string) private addressToDID;
    mapping(bytes32 => VC[]) private userVCs;
    
    // Enhanced mappings for relationships and access control
    mapping(bytes32 => VCRelationship[]) private vcRelationships;
    mapping(string => bytes32[]) private propertyToVCs; // propertyId => VC hashes
    mapping(bytes32 => AccessLog[]) private vcAccessLogs;
    mapping(bytes32 => mapping(address => bool)) private userPermissions; // userDID => accessor => hasPermission

    // Events
    event OrgApproved(string indexed orgDID, address indexed orgAddress, string[] scopes, uint256 trustScore);
    event OrgRevoked(string indexed orgDID, address indexed orgAddress);
    event OrgTrustScoreUpdated(string indexed orgDID, uint256 newTrustScore);
    
    event VCIssued(bytes32 indexed userDID, bytes32 indexed vcHash, string vcType, string indexed issuerDID, string propertyId);
    event VCRevoked(bytes32 indexed userDID, bytes32 indexed vcHash, string indexed issuerDID);
    event VCUpdated(bytes32 indexed userDID, bytes32 indexed vcHash, string indexed issuerDID);
    
    event VCRelationshipCreated(
        bytes32 indexed vcId1, 
        bytes32 indexed vcId2, 
        string relationshipType, 
        string indexed issuerDID,
        string metadata
    );
    event VCRelationshipRevoked(
        bytes32 indexed vcId1, 
        bytes32 indexed vcId2, 
        string indexed issuerDID
    );
    
    event VCAccessAttempt(
        bytes32 indexed userDID,
        bytes32 indexed vcHash,
        address indexed accessor,
        bool success,
        string reason,
        uint256 timestamp
    );
    
    event UserPermissionGranted(bytes32 indexed userDID, address indexed accessor, string scope);
    event UserPermissionRevoked(bytes32 indexed userDID, address indexed accessor, string scope);
    
    event LogAnchorStored(uint256 indexed anchorId, bytes32 logRootHash);

    // Modifiers
    modifier onlyAdmin() {
        require(msg.sender == admin, "Only admin authorized");
        _;
    }

    modifier onlyApprovedOrg(string memory orgDID) {
        require(organizations[orgDID].approved, "Org not approved");
        require(organizations[orgDID].mainAddress == msg.sender, "Sender not authorized for this org DID");
        _;
    }

    modifier onlyVCIssuer(bytes32 userDID, bytes32 vcHash) {
        (bool found, uint256 idx) = _findVC(userDID, vcHash);
        require(found, "VC not found");
        require(keccak256(bytes(userVCs[userDID][idx].issuerDID)) == keccak256(bytes(addressToDID[msg.sender])), "Only issuer can perform this action");
        _;
    }

    constructor() {
        admin = msg.sender;
        totalVCs = 0;
        totalOrganizations = 0;
        totalRelationships = 0;
    }

    // Organization Management
    function approveOrganization(
        string memory orgDID, 
        address orgAddress, 
        string[] memory scopes,
        uint256 trustScore,
        string memory orgType
    ) external onlyAdmin {
        require(trustScore <= 100, "Trust score must be 0-100");
        require(bytes(orgType).length > 0, "Organization type required");
        
        organizations[orgDID].approved = true;
        organizations[orgDID].mainAddress = orgAddress;
        organizations[orgDID].trustScore = trustScore;
        organizations[orgDID].orgType = orgType;
        organizations[orgDID].approvedAt = block.timestamp;
        
        delete organizations[orgDID].scopes;
        for (uint i = 0; i < scopes.length; i++) {
            organizations[orgDID].scopes.push(scopes[i]);
        }
        
        addressToDID[orgAddress] = orgDID;
        totalOrganizations++;
        
        emit OrgApproved(orgDID, orgAddress, scopes, trustScore);
    }

    function revokeOrganization(string memory orgDID) external onlyAdmin {
        address orgAddress = organizations[orgDID].mainAddress;
        organizations[orgDID].approved = false;
        delete organizations[orgDID].scopes;
        organizations[orgDID].trustScore = 0;
        totalOrganizations--;
        
        emit OrgRevoked(orgDID, orgAddress);
    }

    function updateOrgTrustScore(string memory orgDID, uint256 newTrustScore) external onlyAdmin {
        require(organizations[orgDID].approved, "Organization must be approved");
        require(newTrustScore <= 100, "Trust score must be 0-100");
        
        organizations[orgDID].trustScore = newTrustScore;
        emit OrgTrustScoreUpdated(orgDID, newTrustScore);
    }

    function getOrganization(string memory orgDID) external view returns (
        bool approved, 
        address mainAddress, 
        string[] memory scopes,
        uint256 trustScore,
        uint256 approvedAt,
        string memory orgType
    ) {
        Organization storage org = organizations[orgDID];
        return (org.approved, org.mainAddress, org.scopes, org.trustScore, org.approvedAt, org.orgType);
    }

    // VC Management
    function issueVC(
        bytes32 userDID,
        bytes32 vcHash,
        string calldata vcType,
        string calldata propertyId
    ) external {
        string memory orgDID = addressToDID[msg.sender];
        require(bytes(orgDID).length > 0, "Sender not linked to any orgDID");
        require(organizations[orgDID].approved, "Org not approved");
        require(_hasScope(orgDID, vcType), "Org not allowed for this VC type");

        VC memory newVC = VC({
            hash: vcHash,
            vcType: vcType,
            issuedAt: block.timestamp,
            issuerDID: orgDID,
            revoked: false,
            propertyId: propertyId,
            relatedVCs: new bytes32[](0)
        });

        userVCs[userDID].push(newVC);
        totalVCs++;

        // Link to property if provided
        if (bytes(propertyId).length > 0) {
            propertyToVCs[propertyId].push(vcHash);
        }

        emit VCIssued(userDID, vcHash, vcType, orgDID, propertyId);
        emit VCAccessAttempt(userDID, vcHash, msg.sender, true, "VC Issued", block.timestamp);
    }

    function revokeVC(bytes32 userDID, bytes32 vcHash) external onlyVCIssuer(userDID, vcHash) {
        (bool found, uint256 idx) = _findVC(userDID, vcHash);
        require(!userVCs[userDID][idx].revoked, "Already revoked");

        userVCs[userDID][idx].revoked = true;
        
        // Revoke all relationships for this VC
        _revokeAllRelationships(vcHash);
        
        emit VCRevoked(userDID, vcHash, addressToDID[msg.sender]);
        emit VCAccessAttempt(userDID, vcHash, msg.sender, true, "VC Revoked", block.timestamp);
    }

    function updateVC(
        bytes32 userDID,
        bytes32 vcHash,
        bytes32 newVCHash
    ) external onlyVCIssuer(userDID, vcHash) {
        (bool found, uint256 idx) = _findVC(userDID, vcHash);
        require(!userVCs[userDID][idx].revoked, "Cannot update revoked VC");

        userVCs[userDID][idx].hash = newVCHash;
        
        emit VCUpdated(userDID, vcHash, addressToDID[msg.sender]);
        emit VCAccessAttempt(userDID, vcHash, msg.sender, true, "VC Updated", block.timestamp);
    }

    // Relationship Management
    function createVCRelationship(
        bytes32 vcId1,
        bytes32 vcId2,
        string calldata relationshipType,
        string calldata metadata
    ) external {
        string memory orgDID = addressToDID[msg.sender];
        require(organizations[orgDID].approved, "Org not approved");
        
        // Verify both VCs exist and are not revoked
        require(_vcExistsAndActive(vcId1), "VC1 must exist and be active");
        require(_vcExistsAndActive(vcId2), "VC2 must exist and be active");
        
        // Create bidirectional relationship
        vcRelationships[vcId1].push(VCRelationship({
            relatedVCId: vcId2,
            relationshipType: relationshipType,
            createdAt: block.timestamp,
            issuerDID: orgDID,
            active: true,
            metadata: metadata
        }));
        
        vcRelationships[vcId2].push(VCRelationship({
            relatedVCId: vcId1,
            relationshipType: relationshipType,
            createdAt: block.timestamp,
            issuerDID: orgDID,
            active: true,
            metadata: metadata
        }));
        
        totalRelationships += 2;
        
        emit VCRelationshipCreated(vcId1, vcId2, relationshipType, orgDID, metadata);
    }

    function revokeVCRelationship(bytes32 vcId1, bytes32 vcId2) external {
        string memory orgDID = addressToDID[msg.sender];
        require(organizations[orgDID].approved, "Org not approved");
        
        _deactivateRelationship(vcId1, vcId2);
        _deactivateRelationship(vcId2, vcId1);
        
        emit VCRelationshipRevoked(vcId1, vcId2, orgDID);
    }

    function getVCRelationships(bytes32 vcId) external view returns (VCRelationship[] memory) {
        return vcRelationships[vcId];
    }

    function getPropertyVCs(string calldata propertyId) external view returns (bytes32[] memory) {
        return propertyToVCs[propertyId];
    }

    // Enhanced Access Control
    function accessVC(
        bytes32 userDID,
        bytes32 vcHash,
        string calldata requestedScope
    ) external returns (bool) {
        string memory orgDID = addressToDID[msg.sender];
        bool success;
        string memory reason;
        
        if(!organizations[orgDID].approved) {
            success = false;
            reason = "Org not approved";
        } else if (!_hasScope(orgDID, requestedScope)) {
            success = false;
            reason = "Scope not allowed";
        } else {
            (bool found, uint256 idx) = _findVC(userDID, vcHash);
            if(!found) {
                success = false;
                reason = "VC not found";
            } else if(userVCs[userDID][idx].revoked) {
                success = false;
                reason = "VC revoked";
            } else {
                success = true;
                reason = "VC Access Granted";
            }
        }
        
        // Log access attempt
        vcAccessLogs[vcHash].push(AccessLog({
            accessor: msg.sender,
            vcHash: vcHash,
            scope: requestedScope,
            timestamp: block.timestamp,
            success: success,
            reason: reason
        }));
        
        emit VCAccessAttempt(userDID, vcHash, msg.sender, success, reason, block.timestamp);
        return success;
    }

    function accessVCWithRelationships(
        bytes32 userDID,
        bytes32 vcHash,
        string calldata requestedScope
    ) external returns (bool, bytes32[] memory) {
        bool accessGranted = accessVC(userDID, vcHash, requestedScope);
        bytes32[] memory relatedVCs;
        
        if (accessGranted) {
            relatedVCs = _getActiveRelatedVCs(vcHash);
        }
        
        return (accessGranted, relatedVCs);
    }

    // User Permission Management
    function grantUserPermission(
        bytes32 userDID,
        address accessor,
        string calldata scope
    ) external {
        string memory orgDID = addressToDID[msg.sender];
        require(organizations[orgDID].approved, "Org not approved");
        require(_hasScope(orgDID, scope), "Org not allowed for this scope");
        
        userPermissions[userDID][accessor] = true;
        emit UserPermissionGranted(userDID, accessor, scope);
    }

    function revokeUserPermission(
        bytes32 userDID,
        address accessor
    ) external {
        string memory orgDID = addressToDID[msg.sender];
        require(organizations[orgDID].approved, "Org not approved");
        
        userPermissions[userDID][accessor] = false;
        emit UserPermissionRevoked(userDID, accessor, "");
    }

    // Query Functions
    function getUserVCs(bytes32 userDID) external view returns (VC[] memory) {
        return userVCs[userDID];
    }

    function getVCAccessLogs(bytes32 vcHash) external view returns (AccessLog[] memory) {
        return vcAccessLogs[vcHash];
    }

    function hasUserPermission(bytes32 userDID, address accessor) external view returns (bool) {
        return userPermissions[userDID][accessor];
    }

    function getVCByHash(bytes32 vcHash) external view returns (VC memory, bool found) {
        // Search through all users to find VC
        // This is a simplified search - in production, you might want a reverse mapping
        for (uint i = 0; i < 1000; i++) { // Limit search to prevent infinite loops
            bytes32 userDID = bytes32(i);
            VC[] storage vcs = userVCs[userDID];
            for (uint j = 0; j < vcs.length; j++) {
                if (vcs[j].hash == vcHash) {
                    return (vcs[j], true);
                }
            }
        }
        return (VC("", "", 0, "", false, "", new bytes32[](0)), false);
    }

    // Admin Functions
    function anchorLogHash(uint256 anchorId, bytes32 logRootHash) external onlyAdmin {
        emit LogAnchorStored(anchorId, logRootHash);
    }

    function transferAdmin(address newAdmin) external onlyAdmin {
        require(newAdmin != address(0), "Zero address");
        admin = newAdmin;
    }

    function getContractStats() external view returns (
        uint256 _totalVCs,
        uint256 _totalOrganizations,
        uint256 _totalRelationships
    ) {
        return (totalVCs, totalOrganizations, totalRelationships);
    }

    // Internal Helper Functions
    function _hasScope(string memory orgDID, string memory scope) internal view returns (bool) {
        string[] storage orgScopes = organizations[orgDID].scopes;
        for (uint256 i = 0; i < orgScopes.length; i++) {
            if (keccak256(bytes(orgScopes[i])) == keccak256(bytes(scope))) {
                return true;
            }
        }
        return false;
    }

    function _findVC(bytes32 userDID, bytes32 vcHash) internal view returns (bool, uint256) {
        VC[] storage vcs = userVCs[userDID];
        for (uint256 i = 0; i < vcs.length; i++) {
            if (vcs[i].hash == vcHash) {
                return (true, i);
            }
        }
        return (false, 0);
    }

    function _vcExistsAndActive(bytes32 vcHash) internal view returns (bool) {
        (VC memory vc, bool found) = getVCByHash(vcHash);
        return found && !vc.revoked;
    }

    function _deactivateRelationship(bytes32 vcId1, bytes32 vcId2) internal {
        VCRelationship[] storage relationships = vcRelationships[vcId1];
        for (uint256 i = 0; i < relationships.length; i++) {
            if (relationships[i].relatedVCId == vcId2 && relationships[i].active) {
                relationships[i].active = false;
                break;
            }
        }
    }

    function _revokeAllRelationships(bytes32 vcHash) internal {
        VCRelationship[] storage relationships = vcRelationships[vcHash];
        for (uint256 i = 0; i < relationships.length; i++) {
            if (relationships[i].active) {
                relationships[i].active = false;
                // Also deactivate reverse relationship
                _deactivateRelationship(relationships[i].relatedVCId, vcHash);
            }
        }
    }

    function _getActiveRelatedVCs(bytes32 vcHash) internal view returns (bytes32[] memory) {
        VCRelationship[] storage relationships = vcRelationships[vcHash];
        uint256 activeCount = 0;
        
        // Count active relationships
        for (uint256 i = 0; i < relationships.length; i++) {
            if (relationships[i].active) {
                activeCount++;
            }
        }
        
        // Create array of active related VCs
        bytes32[] memory activeVCs = new bytes32[](activeCount);
        uint256 index = 0;
        for (uint256 i = 0; i < relationships.length; i++) {
            if (relationships[i].active) {
                activeVCs[index] = relationships[i].relatedVCId;
                index++;
            }
        }
        
        return activeVCs;
    }
} 