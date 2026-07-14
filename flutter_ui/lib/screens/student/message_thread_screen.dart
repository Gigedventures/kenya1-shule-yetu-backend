/// Message thread screen - Detail layer.
library message_thread_screen;

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../data/providers/messages_provider.dart';
import '../../data/repositories/message_repository.dart';
import '../../services/k1_api_service.dart';
import '../../widgets/density/index.dart';
import '../../theme/density_tokens.dart';
import '../../models/message_models.dart';

class MessageThreadScreen extends StatelessWidget {
  const MessageThreadScreen({super.key, required this.thread});

  final Thread thread;

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => MessagesProvider(
        repository: MessageRepositoryImpl(apiService: context.read<K1ApiService>()),
      )..loadThread(thread.id),
      child: _MessageThreadView(thread: thread),
    );
  }
}

class _MessageThreadView extends StatefulWidget {
  const _MessageThreadView({required this.thread});

  final Thread thread;

  @override
  State<_MessageThreadView> createState() => _MessageThreadViewState();
}

class _MessageThreadViewState extends State<_MessageThreadView> {
  final _messageController = TextEditingController();
  final _scrollController = ScrollController();

  @override
  void dispose() {
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeOut,
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final config = context.density;

    return Scaffold(
      backgroundColor: config.bgBase,
      appBar: AppBar(
        title: Text(widget.thread.participant.name, style: config.title),
        backgroundColor: config.bgSurface,
        elevation: 0,
        scrolledUnderElevation: 1,
        surfaceTintColor: Colors.transparent,
        leading: IconButton(
          icon: Icon(Icons.arrow_back, size: config.iconMd),
          onPressed: () => Navigator.pop(context),
        ),
        actions: [
          if (widget.thread.participant.isOnline)
            Padding(
              padding: EdgeInsets.only(right: config.sm),
              child: Center(
                child: Container(
                  width: 8,
                  height: 8,
                  decoration: BoxDecoration(
                    color: config.success,
                    shape: BoxShape.circle,
                    border: Border.all(color: config.bgSurface, width: 2),
                  ),
                ),
              ),
            ),
          PopupMenuButton<String>(
            icon: Icon(Icons.more_vert, size: config.iconMd),
            onSelected: (value) {
              switch (value) {
                case 'mark_read':
                  context.read<MessagesProvider>().markThreadRead(widget.thread.id);
                  break;
                case 'block':
                  // TODO: Block user
                  break;
              }
            },
            itemBuilder: (context) => [
              PopupMenuItem(value: 'mark_read', child: Text('Mark as read', style: config.body)),
              PopupMenuItem(value: 'block', child: Text('Block', style: config.body.copyWith(color: config.error))),
            ],
          ),
        ],
      ),
      body: Column(
        children: [
          // Messages list
          Expanded(
            child: Consumer<MessagesProvider>(
              builder: (context, provider, _) {
                if (provider.currentMessages.isEmpty && provider.isLoading) {
                  return _buildSkeleton(context);
                }

                return ListView.builder(
                  controller: _scrollController,
                  padding: EdgeInsets.all(config.sm),
                  itemCount: provider.currentMessages.length,
                  itemBuilder: (context, index) {
                    final message = provider.currentMessages[index];
                    final isOwn = message.senderId == /* current user id */ 0; // Would get from auth
                    return _MessageBubble(message: message, isOwn: isOwn, config: config);
                  },
                );
              },
            ),
          ),
          // Message input
          _buildMessageInput(context),
        ],
      ),
    );
  }

  Widget _buildSkeleton(BuildContext context) {
    final config = context.density;
    return Column(
      children: List.generate(5, (index) {
        final isOwn = index % 2 == 0;
        return Container(
          margin: EdgeInsets.only(bottom: config.sm),
          alignment: isOwn ? Alignment.centerRight : Alignment.centerLeft,
          child: Container(
            constraints: BoxConstraints(maxWidth: 300),
            padding: EdgeInsets.symmetric(horizontal: config.sm, vertical: config.xs),
            decoration: BoxDecoration(
              color: isOwn ? config.primaryBg : config.bgSurface,
              borderRadius: BorderRadius.circular(config.radiusMd),
              border: isOwn ? null : Border.all(color: config.borderLight),
            ),
            child: SkeletonLoader(width: 150.0 + (index * 30.0), height: 16),
          ),
        );
      }),
    );
  }

  Widget _buildMessageInput(BuildContext context) {
    final config = context.density;
    final provider = context.read<MessagesProvider>();

    return Container(
      padding: EdgeInsets.all(config.sm),
      decoration: BoxDecoration(
        color: config.bgSurface,
        border: Border(top: BorderSide(color: config.borderLight)),
      ),
      child: Row(
        children: [
          Expanded(
            child: TextField(
              controller: _messageController,
              decoration: InputDecoration(
                hintText: 'Type a message...',
                hintStyle: config.body.copyWith(color: config.textMuted),
                filled: true,
                fillColor: config.bgBase,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(config.radiusPill),
                  borderSide: BorderSide(color: config.borderLight),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(config.radiusPill),
                  borderSide: BorderSide(color: config.borderLight),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(config.radiusPill),
                  borderSide: BorderSide(color: config.primary, width: 2),
                ),
                contentPadding: EdgeInsets.symmetric(horizontal: config.md, vertical: config.sm),
              ),
              style: config.body,
              maxLines: null,
              textInputAction: TextInputAction.send,
              onSubmitted: (_) => _sendMessage(provider),
            ),
          ),
          SizedBox(width: config.sm),
          FilledButton(
            onPressed: provider.isSending ? null : () => _sendMessage(provider),
            style: FilledButton.styleFrom(
              padding: EdgeInsets.all(config.sm),
              shape: CircleBorder(),
            ),
            child: provider.isSending
                ? SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: config.textInverse))
                : Icon(Icons.send, size: config.iconMd),
          ),
        ],
      ),
    );
  }

  void _sendMessage(MessagesProvider provider) {
    final text = _messageController.text.trim();
    if (text.isEmpty) return;

    _messageController.clear();
    provider.sendMessage(widget.thread.id, text).then((_) {
      _scrollToBottom();
    }).catchError((e) {
      // Show error
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Failed to send message: $e')),
      );
    });
  }
}

class _MessageBubble extends StatelessWidget {
  const _MessageBubble({
    required this.message,
    required this.isOwn,
    required this.config,
  });

  final Message message;
  final bool isOwn;
  final DensityConfig config;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: EdgeInsets.only(bottom: config.sm),
      alignment: isOwn ? Alignment.centerRight : Alignment.centerLeft,
      child: Column(
        crossAxisAlignment: isOwn ? CrossAxisAlignment.end : CrossAxisAlignment.start,
        children: [
          if (!isOwn)
            Padding(
              padding: EdgeInsets.only(left: config.sm, bottom: config.xs),
              child: Text(
                message.sender?.name ?? 'Unknown',
                style: config.caption.copyWith(color: config.textMuted, fontWeight: FontWeight.w600),
              ),
            ),
          Container(
            constraints: BoxConstraints(maxWidth: MediaQuery.of(context).size.width * 0.75),
            padding: EdgeInsets.symmetric(horizontal: config.sm, vertical: config.xs),
            decoration: BoxDecoration(
              color: isOwn ? config.primary : config.bgSurface,
              borderRadius: BorderRadius.only(
                topLeft: Radius.circular(config.radiusMd),
                topRight: Radius.circular(config.radiusMd),
                bottomLeft: Radius.circular(isOwn ? config.radiusMd : config.xs),
                bottomRight: Radius.circular(isOwn ? config.xs : config.radiusMd),
              ),
              border: isOwn ? null : Border.all(color: config.borderLight),
            ),
            child: Text(
              message.body,
              style: config.body.copyWith(color: isOwn ? config.textInverse : config.textPrimary),
            ),
          ),
          SizedBox(height: config.xs),
          Padding(
            padding: EdgeInsets.only(left: config.xs, right: config.xs),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(message.formattedTime, style: config.caption.copyWith(color: config.textMuted)),
                if (isOwn) ...[
                  SizedBox(width: config.xs),
                  Icon(
                    message.isRead ? Icons.done_all : Icons.done,
                    size: config.iconXs,
                    color: message.isRead ? config.primary : config.textMuted,
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}